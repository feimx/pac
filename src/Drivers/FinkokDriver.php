<?php

namespace FeiMx\Pac\Drivers;

use FeiMx\Pac\Contracts\PacDriverInterface;
use FeiMx\Pac\Exceptions\PacErrorException;
use FeiMx\Pac\Exceptions\PacVerificationFailedException;
use FeiMx\Pac\PacStamp;
use FeiMx\Pac\PacUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class FinkokDriver extends AbstractDriver implements PacDriverInterface
{
    public function stamp($xml)
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
            throw new PacErrorException(
                $response->stampResult->Incidencias->Incidencia->MensajeIncidencia,
                $response->stampResult->Incidencias->Incidencia->CodigoError
            );
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

        return $this->cancelResultToAttributes($response->cancelResult);
    }

    public function addUser($rfc, $params = [])
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

    public function editUser($rfc, $params = [])
    {
        throw new \Exception('Method editUser() is not implemented.');
    }

    public function getUsers()
    {
        throw new \Exception('Method getUsers() is not implemented.');
    }

    public function getUser($rfc = null)
    {
        throw new \Exception('Method getUser() is not implemented.');
    }

    public function assignStamps($rfc = null, $credit = 0)
    {
        throw new \Exception('Method assignStamps() is not implemented.');
    }

    protected function url($wsdl = null)
    {
        return $this->sandbox
            ? "https://demo-facturacion.finkok.com/servicios/soap/{$wsdl}.wsdl"
            : "https://facturacion.finkok.com/servicios/soap/{$wsdl}.wsdl";
    }

    protected function prepareGenericParams(array $params = [])
    {
        return array_merge([
            'reseller_username' => $this->username,
            'reseller_password' => $this->password,
        ], $params);
    }

    protected function prepareStampParams(array $params = [])
    {
        return array_merge([
            'username' => $this->username,
            'password' => $this->password,
        ], $params);
    }

    protected function stampResultToAttributes($stampResult)
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

    protected function cancelResultToAttributes($cancelResult)
    {
        return [
            'folios' => $cancelResult->Folios,
            'acuse' => $cancelResult->Acuse,
            'date' => Carbon::parse($cancelResult->Fecha),
            'rfc' => $cancelResult->RfcEmisor,
        ];
    }
}
