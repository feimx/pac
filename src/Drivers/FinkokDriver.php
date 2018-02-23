<?php

namespace FeiMx\Pac\Drivers;

use FeiMx\Pac\Contracts\PacDriverInterface;
use FeiMx\Pac\Exceptions\PacVerificationFailedException;
use Illuminate\Support\Facades\Validator;

class FinkokDriver extends AbstractDriver implements PacDriverInterface
{
    // $this->url = $sandbox ? 'http://demo-facturacion.finkok.com/servicios/soap/' : 'https://facturacion.finkok.com/servicios/soap/';

    protected function stamp()
    {
        throw new \Exception('Method stamp() is not implemented.');
    }

    protected function cancel()
    {
        throw new \Exception('Method cancel() is not implemented.');
    }

    protected function addUser($rfc, $params = [])
    {
        if (empty($rfc)) {
            throw new PacVerificationFailedException('The RFC is a necessary fields');
        }

        $rules = [
            'type_user' => 'required|in:O,P',
            'addedd' => 'required',
        ];

        if (Validator::make($params, $rules)->fails()) {
            throw new PacVerificationFailedException('The params did not contain the necessary fields');
        }
    }

    protected function editUser($rfc, $typeUser = 'O', $added)
    {
        throw new \Exception('Method editUser() is not implemented.');
    }

    protected function getUsers()
    {
        throw new \Exception('Method getUsers() is not implemented.');
    }

    protected function getUser($rfc = null)
    {
        throw new \Exception('Method getUser() is not implemented.');
    }

    protected function assignStamps($rfc = null)
    {
        throw new \Exception('Method assignStamps() is not implemented.');
    }
}
