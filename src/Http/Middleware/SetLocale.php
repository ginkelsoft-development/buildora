<?php


namespace Ginkelsoft\Buildora\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        app()->setLocale(session('locale', config('app.locale')));

        return $next($request);
    }
}
