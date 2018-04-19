<?php

namespace FeiMx\Pac\Tests;

use FeiMx\Pac\PacUser;
use FeiMx\Pac\Tests\Fixtures\PacDriverStub;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->driver = new PacDriverStub('user@example.test', '12345678a', $sandbox = true);
    }

    public function testDriverCanCreateNewUser()
    {
        $result = $this->driver->addUser('XAXX010101000', ['X' => [1, 2, 3]]);
        $this->assertInstanceOf(PacUser::class, $result);
    }

    /**
     * @expectedException \FeiMx\Pac\Exceptions\PacErrorException
     */
    public function testExceptionIsThrownIfNotCorrectParams()
    {
        $result = $this->driver->addUser('XAXX010101000', [['X' => [1, 2, 3]]]);
    }

    public function testDriverCanSuspendUser()
    {
        $status = 'S';
        $result = $this->driver->editUser('XAXX010101000', compact('status'));
        $this->assertEquals($status, $result->status);
    }

    public function testDriverCanActiveUser()
    {
        $status = 'A';
        $result = $this->driver->editUser('XAXX010101000', compact('status'));
        $this->assertEquals($status, $result->status);
    }

    public function testDriverCanGetUsers()
    {
        $result = $this->driver->getUsers();
        foreach ($result as $user) {
            $this->assertInstanceOf(PacUser::class, $user);
        }
    }

    public function testDriverCanGetUser()
    {
        $result = $this->driver->getUser($rfc = 'XAXX010101000');
        $this->assertEquals($rfc, $result->rfc);
    }

    public function testDriverCanAssignCreditToUser()
    {
        $result = $this->driver->assignStamps('XAXX010101000', $credit = 100);
        $this->assertEquals($credit, $result->credit);
    }
}
