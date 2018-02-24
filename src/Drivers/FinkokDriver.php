<?php

namespace FeiMx\Pac\Drivers;

use FeiMx\Pac\Contracts\PacDriverInterface;
use FeiMx\Pac\Exceptions\PacErrorException;
use FeiMx\Pac\Exceptions\PacVerificationFailedException;
use FeiMx\Pac\PacUser;
use Illuminate\Support\Facades\Validator;

class FinkokDriver extends AbstractDriver implements PacDriverInterface
{
    public function stamp()
    {
        throw new \Exception('Method stamp() is not implemented.');
    }

    public function cancel()
    {
        throw new \Exception('Method cancel() is not implemented.');
    }

    public function addUser($rfc, $params = [])
    {
        if (empty($rfc)) {
            throw new PacVerificationFailedException('The RFC is a necessary fields');
        }

        $rules = [
            'type_user' => 'required|in:O,P',
            'added' => 'required',
        ];

        if (Validator::make($params, $rules)->fails()) {
            throw new PacVerificationFailedException('The params did not contain the necessary fields');
        }

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

        if ($response->addResult->message == 'Account Already exists') {
            throw new PacErrorException('The RFC has been registered before');
        }

        return (new PacUser)->map(array_merge(['rfc' => $rfc], $params));
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
}
