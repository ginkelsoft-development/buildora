<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

class PermissionSyncController
{
    /**
     * Sync permissions by running the buildora:sync-permissions command.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            // Create a buffered output to capture command output
            $output = new BufferedOutput();

            // Run the sync command
            $exitCode = Artisan::call('buildora:sync-permissions', [], $output);

            // Get the command output
            $commandOutput = $output->fetch();

            // Parse the output to get individual lines
            $lines = array_filter(explode("\n", $commandOutput));

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permissions gesynchroniseerd',
                    'output' => $lines,
                    'details' => $this->parseOutput($lines),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het synchroniseren van permissions',
                'output' => $lines,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Permission sync failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Fout: ' . $e->getMessage(),
                'output' => [],
            ], 500);
        }
    }

    /**
     * Parse the command output to extract useful information.
     *
     * @param array $lines
     * @return array
     */
    protected function parseOutput(array $lines): array
    {
        $registered = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            if (str_contains($line, '✓ Registered:')) {
                $registered++;
            } elseif (str_contains($line, '⊗ Already exists:')) {
                $skipped++;
            }
        }

        return [
            'registered' => $registered,
            'skipped' => $skipped,
            'total' => $registered + $skipped,
        ];
    }
}
