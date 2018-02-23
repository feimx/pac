<?php

namespace FeiMx\Pac;

use FeiMx\Pac\Contracts\Factory;

class PacManager implements Factory
{
    protected $app;
    
    /**
     * Create a new PacManager Instance.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function driver($driver = null)
    {
        return $phrase;
    }
}
