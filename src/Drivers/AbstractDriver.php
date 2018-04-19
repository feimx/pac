<?php

namespace FeiMx\Pac\Drivers;

use GuzzleHttp\Client;
use FeiMx\Pac\Contracts\PacDriverInterface;
use Meng\AsyncSoap\Guzzle\Factory as SoapFactory;

abstract class AbstractDriver implements PacDriverInterface
{
    /**
     * The driver username.
     *
     * @var string
     */
    protected $username;
    /**
     * The driver password.
     *
     * @var string
     */
    protected $password;
    /**
     * The driver sandbox.
     *
     * @var bool
     */
    protected $sandbox;
    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];
    /**
     * The Guzzle Soap Factory.
     *
     * @var \Meng\AsyncSoap\Guzzle\Factory
     */
    protected $factory;

    /**
     * Create a new driver instance.
     *
     * @param string $username
     * @param string $password
     * @param bool $sandbox
     */
    public function __construct($username, $password, $sandbox = true)
    {
        $this->username = $username;
        $this->password = $password;
        $this->sandbox = $sandbox;
        $this->factory = new SoapFactory();
    }

    abstract public function stamp($xml);

    abstract public function cancel(array $uuids, $rfc, $cer, $key);

    abstract public function addUser($rfc, $params = []);

    abstract public function editUser($rfc, $params = []);

    abstract public function getUsers();

    abstract public function getUser($rfc = null);

    abstract public function assignStamps($rfc = null, $credit = 0);

    abstract protected function url($wsdl = null);

    public function request($url = null, $method = null, $params = [])
    {
        $url = $url ?? $this->url();

        try {
            $response = $this->factory->create(new Client(), $url)
                        ->{$method}($params);

            return $response->wait();
        } catch (\SoapFault $e) {
            return $e;
        }
    }

    public function xml()
    {
        throw new \Exception('Method xml() is not implemented.');
    }

    public function user()
    {
        throw new \Exception('Method user() is not implemented.');
    }
}
