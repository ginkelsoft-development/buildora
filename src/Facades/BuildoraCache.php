<?php

namespace Ginkelsoft\Buildora\Facades;

use Illuminate\Support\Facades\Facade;

class BuildoraCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'buildora.cache';
    }
}
