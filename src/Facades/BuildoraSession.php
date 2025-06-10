<?php

namespace Ginkelsoft\Buildora\Facades;

use Illuminate\Support\Facades\Facade;

class BuildoraSession extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'buildora.session';
    }
}
