<?php

namespace FeiMx\Pac;

use FeiMx\Pac\Contracts\Factory;
use FeiMx\Pac\Drivers\FinkokDriver;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class PacManager extends Manager implements Factory
{
    protected $app;

    /**
     * Create a new manager instance.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    protected function createFinkokDriver()
    {
        $config = $this->app['config']['pac.finkok'];

        return $this->buildDriver(
            FinkokDriver::class,
            $config
        );
    }

    public function buildDriver($driver, $config)
    {
        return new $driver(
            $config['username'],
            $config['password'],
            $config['sandbox']
        );
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Pac driver was specified.');
    }
}
