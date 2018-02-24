<?php

namespace FeiMx\Pac\Tests\Fixtures;

use FeiMx\Pac\Contracts\PacDriverInterface;
use FeiMx\Pac\Drivers\AbstractDriver;
use FeiMx\Pac\Exceptions\PacErrorException;
use FeiMx\Pac\PacUser;

class PacDriverStub extends AbstractDriver implements PacDriverInterface
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
        $response = $this->request(null, 'GetStatistics', $params);
        if (is_a($response, 'SoapFault')) {
            throw new PacErrorException('Error Processing Request');
        }

        return (new PacUser)->map($params);
    }

    public function editUser($rfc, $params = [])
    {
         return (new PacUser)->map($params);
    }

    public function getUsers()
    {
        return collect(range(1, rand(5, 10)))->map(function($index) {
            return (new PacUser)->map([
                'rfc' => 'XAXX01010100'.($index == 1 ? 0 : rand(1, 5))
            ]);
        });
    }

    public function getUser($rfc = null)
    {
        return $this->getUsers()->where('rfc', $rfc)->first();
    }

    public function assignStamps($rfc = null, $credit = 0)
    {
         return $this->getUser($rfc)->map(compact('credit'));
    }

    protected function url($wsdl = null)
    {
        return 'http://www.webservicex.net/Statistics.asmx?WSDL';
    }
}
