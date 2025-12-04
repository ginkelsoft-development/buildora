<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;

class BuildoraSettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index()
    {
        $stats = $this->getSystemStats();

        return view('buildora::settings.index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Sync permissions via AJAX.
     */
    public function syncPermissions(Request $request): JsonResponse
    {
        try {
            // Run the sync permissions command
            Artisan::call('buildora:sync-permissions');
            $output = Artisan::output();

            // Get updated permission count
            $permissionCount = Permission::count();

            return response()->json([
                'success' => true,
                'message' => __buildora('Permissions synchronized successfully.'),
                'output' => $output,
                'permission_count' => $permissionCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => __buildora('Cache cleared successfully.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system information.
     */
    public function systemInfo(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSystemStats(),
        ]);
    }

    /**
     * Get system statistics.
     */
    protected function getSystemStats(): array
    {
        $permissionCount = 0;
        $userCount = 0;
        $resourceCount = 0;

        try {
            $permissionCount = Permission::count();
        } catch (\Exception $e) {
            // Permission table might not exist
        }

        try {
            $userModel = config('auth.providers.users.model', 'App\\Models\\User');
            if (class_exists($userModel)) {
                $userCount = $userModel::count();
            }
        } catch (\Exception $e) {
            // User table might not exist
        }

        // Count Buildora resources
        $resourcePath = app_path('Buildora/Resources');
        if (is_dir($resourcePath)) {
            $resourceCount = count(glob($resourcePath . '/*Buildora.php'));
        }

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'buildora_version' => config('buildora.version', 'dev'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'permission_count' => $permissionCount,
            'user_count' => $userCount,
            'resource_count' => $resourceCount,
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
        ];
    }
}
