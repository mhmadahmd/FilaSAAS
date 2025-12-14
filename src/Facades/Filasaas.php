<?php

namespace Mhmadahmd\Filasaas\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mhmadahmd\Filasaas\Filasaas
 */
class Filasaas extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mhmadahmd\Filasaas\Filasaas::class;
    }
}
