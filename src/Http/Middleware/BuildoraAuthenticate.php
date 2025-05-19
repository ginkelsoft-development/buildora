<?php

namespace Ginkelsoft\Buildora\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class BuildoraAuthenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request The current HTTP request.
     * @return string|null The login route if the request does not expect JSON; otherwise, null.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            return route('buildora.login');
        }

        return null;
    }
}
