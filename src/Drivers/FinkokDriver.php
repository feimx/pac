<?php

namespace FeiMx\Pac\Drivers;

use ArrayAccess;
use FeiMx\Pac\Contracts\PacDriverInterface;
use FeiMx\Pac\Exceptions\CfdiAlreadyCanceledException;
use FeiMx\Pac\Exceptions\CfdiInProcessException;
use FeiMx\Pac\Exceptions\CfdiNotCancelableException;
use FeiMx\Pac\Exceptions\PacErrorException;
use FeiMx\Pac\Exceptions\PacVerificationFailedException;
use FeiMx\Pac\PacStamp;
use FeiMx\Pac\PacUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class FinkokDriver extends AbstractDriver implements PacDriverInterface
{
    public function stamp($xml): PacStamp
    {
        $response = $this->request(
            $this->url('stamp'),
            'Stamp',
            $this->prepareStampParams(['xml' => $xml])
        );

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        if (!isset($response->stampResult->UUID)) {
            throw new PacErrorException($response->stampResult->Incidencias->Incidencia->MensajeIncidencia);
        }

        return (new PacStamp())->map($this->stampResultToAttributes($response->stampResult));
    }

    public function cancel(array $uuids, $rfc, $cer, $key)
    {
        $response = $this->request(
            $this->url('cancel'),
            'cancel',
            $this->prepareStampParams([
                'UUIDS' => ['uuids' => $uuids],
                'taxpayer_id' => $rfc,
                'cer' => $cer,
                'key' => $key,
            ])
        );

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        if (isset($response->cancelResult->CodEstatus)) {
            throw new PacErrorException($response->cancelResult->CodEstatus);
        }

        $statusUuid = $response->cancelResult->Folios->Folio->EstatusUUID ?? null;

        if ($statusUuid && in_array($statusUuid, [708, 205])) {
            throw new PacErrorException('Ocurrio un error con el SAT, al intentar cancelar el comprobante.');
        }

        $canceledStatus = $response->cancelResult->Folios->Folio->EstatusCancelacion ?? null;
        if ($canceledStatus && preg_match('/Cancelación|Cancelado/', $canceledStatus)) {
            throw new CfdiAlreadyCanceledException('El comprobante ya ha sido cancelado.');
        }

        if ($statusUuid && 'no_cancelable' == $statusUuid) {
            throw new CfdiNotCancelableException();
        }

        if ($statusUuid && 'En proceso' == $statusUuid) {
            throw new CfdiInProcessException();
        }

        return $this->cancelResultToAttributes($response->cancelResult);
    }

    public function addUser($rfc, $params = []): PacUser
    {
        $this->throwErrorIfInvalidParams($data = array_merge(['rfc' => $rfc], $params));

        $response = $this->request(
            $this->url('registration'),
            'add',
            $this->prepareGenericParams(array_merge(['taxpayer_id' => $rfc], $params))
        );

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        if (!$response->addResult->success) {
            throw new PacErrorException($response->addResult->message);
        }

        if ('Account Already exists' == $response->addResult->message) {
            throw new PacErrorException('The RFC has been registered before');
        }

        return (new PacUser())->map($data);
    }

    public function editUser($rfc, $params = [])
    {
        $response = $this->request(
            $this->url('registration'),
            'edit',
            $this->prepareGenericParams(array_merge(['taxpayer_id' => $rfc], $params))
        );

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        if (!$response->editResult->success) {
            throw new PacErrorException($response->editResult->message);
        }

        return $response->editResult->message;
    }

    public function getUsers(): ArrayAccess
    {
        $response = $this->request($this->url('registration'), 'get', $this->prepareGenericParams(['taxpayer_id' => '']));

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        $users = collect([]);
        foreach ($response->getResult->users->ResellerUser as $resellerUser) {
            $users->push(
                (new PacUser())->map(
                    $this->mapToAttributes($resellerUser)
                )
            );
        }

        return $users;
    }

    public function getUser($rfc = null): PacUser
    {
        $response = $this->request($this->url('registration'), 'get', $this->prepareGenericParams(['taxpayer_id' => $rfc]));

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        return (new PacUser())->map(
            $this->mapToAttributes($response->getResult->users->ResellerUser)
        );
    }

    public function assignStamps($rfc = null, $credit = 0)
    {
        $response = $this->request(
            $this->url('registration'),
            'assign',
            $this->prepareStampParams(['taxpayer_id' => $rfc, 'credit' => $credit])
        );

        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException($response->faultstring);
        }

        if (!$response->assignResult->success) {
            throw new PacErrorException($response->assignResult->message);
        }

        return $response->assignResult->message;
    }

    protected function url($wsdl = null)
    {
        return $this->sandbox
            ? "https://demo-facturacion.finkok.com/servicios/soap/{$wsdl}.wsdl"
            : "https://facturacion.finkok.com/servicios/soap/{$wsdl}.wsdl";
    }

    protected function throwErrorIfInvalidParams($params = [])
    {
        $rules = [
            'rfc' => 'required',
            'type_user' => 'required|in:O,P',
            'added' => 'required',
        ];

        if (Validator::make($params, $rules)->fails()) {
            throw new PacVerificationFailedException('The params did not contain the necessary fields');
        }
    }

    protected function prepareGenericParams(array $params = []): array
    {
        return array_merge([
            'reseller_username' => $this->username,
            'reseller_password' => $this->password,
        ], $params);
    }

    protected function prepareStampParams(array $params = []): array
    {
        return array_merge([
            'username' => $this->username,
            'password' => $this->password,
        ], $params);
    }

    protected function stampResultToAttributes($stampResult): array
    {
        return [
            'xml' => $stampResult->xml,
            'uuid' => $stampResult->UUID,
            'date' => Carbon::parse($stampResult->Fecha),
            'statusCode' => $stampResult->CodEstatus,
            'satSeal' => $stampResult->SatSeal,
            'satCertificateNumber' => $stampResult->NoCertificadoSAT,
        ];
    }

    protected function cancelResultToAttributes($cancelResult): array
    {
        return [
            'folios' => $cancelResult->Folios,
            'acuse' => $cancelResult->Acuse,
            'date' => Carbon::parse($cancelResult->Fecha),
            'rfc' => $cancelResult->RfcEmisor,
        ];
    }

    protected function mapToAttributes($resellerUser): array
    {
        return [
            'status' => $resellerUser->status,
            'counter' => $resellerUser->counter,
            'credit' => $resellerUser->credit,
            'rfc' => $resellerUser->taxpayer_id,
        ];
    }
}
