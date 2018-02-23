<?php

namespace FeiMx\Pac\Drivers;

use FeiMx\Pac\Contracts\PacDriverInterface;

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
     * @var string
     */
    protected $sandbox;
    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Create a new driver instance.
     *
     * @param string $username
     * @param string $clientSecret
     * @param string $sandbox
     */
    public function __construct($username, $password, $sandbox = true)
    {
        $this->username = $username;
        $this->password = $password;
        $this->sandbox = $sandbox;
    }

    abstract protected function stamp();

    abstract protected function cancel();

    abstract protected function addUser($rfc, $params = []);

    abstract protected function editUser($rfc, $typeUser, $added);

    abstract protected function getUsers();

    abstract protected function getUser($rfc = null);

    abstract protected function assignStamps($rfc = null);

    public function xml()
    {
        throw new \Exception('Method xml() is not implemented.');
    }

    public function user()
    {
        throw new \Exception('Method user() is not implemented.');
    }
}
