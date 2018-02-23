<?php

namespace FeiMx\Pac;

use FeiMx\Pac\Contracts\Factory;
use Illuminate\Support\Facades\Facade;

/**
 * @see \FeiMx\Pac\Pac
 */
class PacFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
