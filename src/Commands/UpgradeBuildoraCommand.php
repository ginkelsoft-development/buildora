<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UpgradeBuildoraCommand extends Command
{
    protected $signature = 'buildora:upgrade
                            {--force : Force upgrade without confirmation}';

    protected $description = 'Upgrade Buildora to the latest version - runs migrations and updates User model';

    protected array $upgradedComponents = [];

    public function handle(): int
    {
        $this->showHeader();

        if (!$this->option('force') && !$this->confirm('This will upgrade Buildora. Continue?', true)) {
            $this->info('Upgrade cancelled.');
            return self::SUCCESS;
        }

        try {
            // Step 1: Run migrations
            $this->upgrade('Running database migrations', fn() => $this->runMigrations());

            // Step 2: Update User model with 2FA fields
            $this->upgrade('Updating User model', fn() => $this->updateUserModel());

            // Step 3: Sync permissions
            $this->upgrade('Syncing permissions', fn() => $this->syncPermissions());

            // Step 4: Clear caches
            $this->upgrade('Clearing caches', fn() => $this->clearCaches());

            $this->showSuccess();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Upgrade failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function showHeader(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                   Buildora Upgrade Wizard                    ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $currentVersion = config('buildora.version', 'unknown');
        $this->line("  Current version: <info>{$currentVersion}</info>");
        $this->newLine();
    }

    protected function upgrade(string $description, callable $callback): void
    {
        $this->line("  <fg=blue>→</> {$description}...");
        $callback();
        $this->upgradedComponents[] = $description;
    }

    protected function runMigrations(): void
    {
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->line("    <fg=yellow>!</> Database not available - skipping migrations");
            $this->line("    <fg=yellow>!</> Run 'php artisan migrate' manually when database is available");
            return;
        }

        Artisan::call('migrate', ['--force' => true]);

        $output = trim(Artisan::output());
        if (!empty($output) && !Str::contains($output, 'Nothing to migrate')) {
            collect(explode("\n", $output))->each(function ($line) {
                if (!empty(trim($line))) {
                    $this->line("    {$line}");
                }
            });
        }

        $this->line("    <fg=green>✓</> Migrations completed");
    }

    protected function updateUserModel(): void
    {
        $path = app_path('Models/User.php');

        if (!File::exists($path)) {
            $this->line("    <fg=yellow>!</> User model not found at {$path}");
            return;
        }

        $contents = File::get($path);
        $modified = false;

        // Check for 2FA fields in $fillable
        $twoFactorFields = [
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ];

        $fieldsToAdd = [];
        foreach ($twoFactorFields as $field) {
            if (!Str::contains($contents, "'{$field}'") && !Str::contains($contents, "\"{$field}\"")) {
                $fieldsToAdd[] = $field;
            }
        }

        if (!empty($fieldsToAdd)) {
            if (preg_match('/\$fillable\s*=\s*\[([^\]]*)\]/s', $contents, $matches)) {
                $existingFields = $matches[1];
                $newFields = implode(",\n        ", array_map(fn($f) => "'{$f}'", $fieldsToAdd));

                $replacement = rtrim($existingFields);
                if (!empty(trim($replacement))) {
                    $replacement .= ",\n        " . $newFields;
                } else {
                    $replacement = "\n        " . $newFields . "\n    ";
                }

                $contents = preg_replace(
                    '/\$fillable\s*=\s*\[([^\]]*)\]/s',
                    '$fillable = [' . $replacement . ']',
                    $contents
                );
                $modified = true;
                $this->line("    <fg=green>✓</> Added 2FA fields to \$fillable");
            } else {
                $this->line("    <fg=yellow>!</> Could not find \$fillable array");
                $this->line("      Please add manually: " . implode(', ', $fieldsToAdd));
            }
        } else {
            $this->line("    <fg=green>✓</> 2FA fields already present");
        }

        // Check for HasRoles trait
        if (!Str::contains($contents, 'HasRoles')) {
            $contents = Str::replaceFirst(
                '{',
                "{\n    use \\Spatie\\Permission\\Traits\\HasRoles;",
                $contents
            );
            $modified = true;
            $this->line("    <fg=green>✓</> Added HasRoles trait");
        }

        // Check for HasBuildora trait
        if (!Str::contains($contents, 'HasBuildora')) {
            $contents = Str::replaceFirst(
                '{',
                "{\n    use \\Ginkelsoft\\Buildora\\Traits\\HasBuildora;",
                $contents
            );
            $modified = true;
            $this->line("    <fg=green>✓</> Added HasBuildora trait");
        }

        if ($modified) {
            File::put($path, $contents);
            $this->line("    <fg=green>✓</> User model updated");
        }
    }

    protected function syncPermissions(): void
    {
        try {
            \DB::connection()->getPdo();
            Artisan::call('buildora:sync-permissions');
            $this->line("    <fg=green>✓</> Permissions synced");
        } catch (\Exception $e) {
            $this->line("    <fg=yellow>!</> Database not available - skipping permission sync");
            $this->line("    <fg=yellow>!</> Run 'php artisan buildora:sync-permissions' manually");
        }
    }

    protected function clearCaches(): void
    {
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        try {
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            // Cache driver might need database, ignore
        }

        $this->line("    <fg=green>✓</> Caches cleared");
    }

    protected function showSuccess(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║   <fg=green>✓ Buildora upgraded successfully!</>                           ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->info('  Completed:');
        foreach ($this->upgradedComponents as $component) {
            $this->line("    • {$component}");
        }

        $this->newLine();
        $this->info('  Note: If you have customized views, please check for any changes.');
        $this->newLine();
    }
}
