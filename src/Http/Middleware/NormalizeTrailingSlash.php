<?php

namespace Ginkelsoft\Buildora\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to normalize URLs with trailing slashes.
 *
 * Strips trailing slashes internally so Laravel routes match correctly,
 * regardless of whether the web server adds them.
 */
class NormalizeTrailingSlash
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $uri = $request->getRequestUri();

        // Strip trailing slash internally (don't redirect, just normalize)
        if ($uri !== '/' && str_ends_with(strtok($uri, '?'), '/')) {
            $path = rtrim(strtok($uri, '?'), '/');
            $query = $request->getQueryString();
            $newUri = $query ? $path . '?' . $query : $path;

            $request->server->set('REQUEST_URI', $newUri);
            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
        }

        return $next($request);
    }
}