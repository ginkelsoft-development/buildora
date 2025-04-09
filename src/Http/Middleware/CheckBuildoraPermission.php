<?php

namespace Ginkelsoft\Buildora\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBuildoraPermission
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        logger()->info('▶️ BuildoraPermission middleware start', [
            'user_id' => auth()->id(),
            'resource' => $request->route('resource'),
            'action' => $action,
        ]);

        $resource = $request->route('resource');

        if (! $resource) {
            logger()->warning('❌ BuildoraPermission failed', [
                'user_id-1' => auth()->id(),
                'permission' => "{$resource}.{$action}",
            ]);
            abort(403, 'No resource specified.');
        }

        $permission = "{$resource}.{$action}";

        if (! auth()->user()?->can($permission)) {
            logger()->warning('❌ BuildoraPermission failed', [
                'user_id' => auth()->id(),
                'permission' => "{$resource}.{$action}",
            ]);
            abort(403, "Unauthorized for permission: {$permission}");
        }

        logger()->info('✅ BuildoraPermission middleware passed');
        return $next($request);
    }
}
