<?php

namespace Ginkelsoft\Buildora\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserResourceExists
{
    /**
     * Handle an incoming request.
     *
     * If the `UserBuildora` resource does not exist, the request is redirected to the installation page,
     * unless it is already accessing a route starting with `buildora/install`.
     *
     * @param Request $request The current HTTP request.
     * @param Closure $next The next middleware handler.
     * @return Response The HTTP response after processing the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->userResourceAvailable()) {
            if (! $request->is('buildora/install*')) {
                /*return redirect()->route('buildora.install')->with(
                    'info',
                    'You must complete the Buildora installation before accessing this area.'
                );*/
            }
        }

        return $next($request);
    }

    private function userResourceAvailable(): bool
    {
        if (class_exists('App\\Buildora\\Resources\\UserBuildora')) {
            return true;
        }

        $config = config('buildora.resources.defaults.user');

        if (($config['enabled'] ?? false) !== true) {
            return false;
        }

        $class = $config['class'] ?? null;

        return $class && class_exists($class);
    }
}
