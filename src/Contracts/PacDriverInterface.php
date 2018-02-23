<?php

namespace FeiMx\Pac\Contracts;

interface PacDriverInterface
{
    public function stamp();

    public function cancel();

    public function addUser($rfc, $typeUser = 'O', $added);

    public function editUser($rfc, $typeUser = 'O', $added);

    public function getUsers();

    public function getUser($rfc = null);

    public function assignStamps($rfc = null);
}
