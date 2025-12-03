<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

class InstallBuildoraCommand extends Command
{
    protected $signature = 'buildora:install
                            {--fresh : Run fresh installation (drop existing resources)}
                            {--skip-user : Skip creating admin user}
                            {--skip-resources : Skip generating resources for models}';

    protected $description = 'Interactive installer for Buildora - sets up everything in one command';

    protected int $currentStep = 0;
    protected int $totalSteps = 10;
    protected array $installedComponents = [];

    public function handle(): int
    {
        $this->showWelcome();

        if (!$this->confirmStart()) {
            return self::SUCCESS;
        }

        try {
            // Step 1: Environment check
            $this->step('Checking environment', fn() => $this->checkEnvironment());

            // Step 2: Check dependencies
            $this->step('Checking dependencies', fn() => $this->checkDependencies());

            // Step 3: Publish config
            $this->step('Publishing configuration', fn() => $this->publishConfig());

            // Step 4: Run migrations
            $this->step('Running database migrations', fn() => $this->runMigrations());

            // Step 5: Setup User model
            $this->step('Configuring User model', fn() => $this->setupUserModel());

            // Step 6: Create resources directory
            $this->step('Setting up Buildora directory', fn() => $this->setupBuildoraDirectory());

            // Step 7: Generate resources
            $this->step('Generating Buildora resources', fn() => $this->generateResources());

            // Step 8: Generate permissions
            $this->step('Generating permissions', fn() => $this->generatePermissions());

            // Step 9: Create admin user
            $this->step('Creating admin user', fn() => $this->createAdminUser());

            // Step 10: Final setup
            $this->step('Finalizing installation', fn() => $this->finalizeInstallation());

            $this->showSuccess();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Installation failed: {$e->getMessage()}");
            $this->newLine();
            $this->warn("You can try running 'php artisan buildora:install' again.");

            return self::FAILURE;
        }
    }

    protected function showWelcome(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                              ║');
        $this->line('║   ██████╗ ██╗   ██╗██╗██╗     ██████╗  ██████╗ ██████╗  █████╗  ║');
        $this->line('║   ██╔══██╗██║   ██║██║██║     ██╔══██╗██╔═══██╗██╔══██╗██╔══██╗ ║');
        $this->line('║   ██████╔╝██║   ██║██║██║     ██║  ██║██║   ██║██████╔╝███████║ ║');
        $this->line('║   ██╔══██╗██║   ██║██║██║     ██║  ██║██║   ██║██╔══██╗██╔══██║ ║');
        $this->line('║   ██████╔╝╚██████╔╝██║███████╗██████╔╝╚██████╔╝██║  ██║██║  ██║ ║');
        $this->line('║   ╚═════╝  ╚═════╝ ╚═╝╚══════╝╚═════╝  ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝ ║');
        $this->line('║                                                              ║');
        $this->line('║                    Installation Wizard                       ║');
        $this->line('║                                                              ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $version = $this->getBuildoraVersion();
        $this->line("  Version: <info>{$version}</info>");
        $this->line("  Laravel: <info>" . app()->version() . "</info>");
        $this->line("  PHP: <info>" . PHP_VERSION . "</info>");
        $this->newLine();
    }

    protected function confirmStart(): bool
    {
        $env = app()->environment();

        if ($env === 'production') {
            $this->warn('  ⚠ You are running in PRODUCTION environment!');
            $this->newLine();
            if (!$this->confirm('Are you sure you want to continue?', false)) {
                $this->info('Installation cancelled.');
                return false;
            }
        }

        $this->info('This wizard will:');
        $this->line('  • Publish Buildora configuration');
        $this->line('  • Run database migrations');
        $this->line('  • Configure your User model with required traits');
        $this->line('  • Generate Buildora resources for your models');
        $this->line('  • Set up permissions');
        $this->line('  • Create an admin user');
        $this->newLine();

        return $this->confirm('Ready to start installation?', true);
    }

    protected function step(string $description, callable $callback): void
    {
        $this->currentStep++;
        $this->newLine();
        $this->line("  <bg=blue;fg=white> STEP {$this->currentStep}/{$this->totalSteps} </> {$description}");
        $this->line('  ' . str_repeat('─', 50));

        $callback();

        $this->installedComponents[] = $description;
    }

    protected function checkEnvironment(): void
    {
        $checks = [
            'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'Laravel >= 10.0' => version_compare(app()->version(), '10.0.0', '>='),
            'Database connection' => $this->checkDatabaseConnection(),
        ];

        foreach ($checks as $check => $passed) {
            if ($passed) {
                $this->line("  <fg=green>✓</> {$check}");
            } else {
                $this->line("  <fg=red>✗</> {$check}");
                throw new \Exception("Environment check failed: {$check}");
            }
        }
    }

    protected function checkDatabaseConnection(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkDependencies(): void
    {
        // Check Spatie Permission
        if (!class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            $this->line("  <fg=yellow>!</> spatie/laravel-permission not found");

            if ($this->confirm('  Install spatie/laravel-permission now?', true)) {
                $this->line("  Installing spatie/laravel-permission...");
                exec('composer require spatie/laravel-permission 2>&1', $output, $code);

                if ($code !== 0) {
                    throw new \Exception('Failed to install spatie/laravel-permission');
                }

                $this->line("  <fg=green>✓</> spatie/laravel-permission installed");

                // Publish Spatie migrations
                Artisan::call('vendor:publish', [
                    '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                ]);
                $this->line("  <fg=green>✓</> Spatie Permission config published");
            } else {
                throw new \Exception('spatie/laravel-permission is required');
            }
        } else {
            $this->line("  <fg=green>✓</> spatie/laravel-permission installed");
        }

        // Check User model
        if (!class_exists(\App\Models\User::class)) {
            throw new \Exception('User model not found at App\Models\User');
        }
        $this->line("  <fg=green>✓</> User model found");

        // Check Livewire
        if (!class_exists(\Livewire\Livewire::class)) {
            $this->line("  <fg=yellow>!</> livewire/livewire not found");

            if ($this->confirm('  Install livewire/livewire now?', true)) {
                exec('composer require livewire/livewire 2>&1', $output, $code);

                if ($code !== 0) {
                    throw new \Exception('Failed to install livewire/livewire');
                }
                $this->line("  <fg=green>✓</> livewire/livewire installed");
            } else {
                throw new \Exception('livewire/livewire is required');
            }
        } else {
            $this->line("  <fg=green>✓</> livewire/livewire installed");
        }
    }

    protected function publishConfig(): void
    {
        $configPath = config_path('buildora.php');

        if (File::exists($configPath) && !$this->option('fresh')) {
            $this->line("  <fg=yellow>!</> Configuration already exists");

            if (!$this->confirm('  Overwrite existing configuration?', false)) {
                $this->line("  <fg=blue>→</> Keeping existing configuration");
                return;
            }
        }

        Artisan::call('vendor:publish', [
            '--tag' => 'buildora-config',
            '--force' => true,
        ]);

        $this->line("  <fg=green>✓</> Configuration published to config/buildora.php");
    }

    protected function runMigrations(): void
    {
        $this->line("  Running migrations...");

        Artisan::call('migrate', ['--force' => true]);

        $output = trim(Artisan::output());
        if (!empty($output)) {
            collect(explode("\n", $output))->each(function ($line) {
                if (!empty(trim($line))) {
                    $this->line("  {$line}");
                }
            });
        }

        $this->line("  <fg=green>✓</> Migrations completed");
    }

    protected function setupUserModel(): void
    {
        $path = app_path('Models/User.php');

        if (!File::exists($path)) {
            throw new \Exception('User model not found at ' . $path);
        }

        $contents = File::get($path);
        $modified = false;

        // Add HasRoles trait
        if (!Str::contains($contents, 'HasRoles')) {
            $contents = Str::replaceFirst(
                '{',
                "{\n    use \\Spatie\\Permission\\Traits\\HasRoles;",
                $contents
            );
            $modified = true;
            $this->line("  <fg=green>✓</> Added HasRoles trait");
        } else {
            $this->line("  <fg=blue>→</> HasRoles trait already present");
        }

        // Add HasBuildora trait
        if (!Str::contains($contents, 'HasBuildora')) {
            $contents = Str::replaceFirst(
                '{',
                "{\n    use \\Ginkelsoft\\Buildora\\Traits\\HasBuildora;",
                $contents
            );
            $modified = true;
            $this->line("  <fg=green>✓</> Added HasBuildora trait");
        } else {
            $this->line("  <fg=blue>→</> HasBuildora trait already present");
        }

        // Add 2FA fields to $fillable
        $twoFactorFields = [
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ];

        $fieldsAdded = [];
        foreach ($twoFactorFields as $field) {
            if (!Str::contains($contents, "'{$field}'") && !Str::contains($contents, "\"{$field}\"")) {
                $fieldsAdded[] = $field;
            }
        }

        if (!empty($fieldsAdded)) {
            // Find the $fillable array and add the fields
            if (preg_match('/\$fillable\s*=\s*\[(.*?)\]/s', $contents, $matches)) {
                $existingFields = $matches[1];
                $newFields = implode(",\n        ", array_map(fn($f) => "'{$f}'", $fieldsAdded));

                // Add new fields to the end of the array
                $replacement = rtrim($existingFields);
                if (!empty(trim($replacement))) {
                    $replacement .= ",\n        " . $newFields;
                } else {
                    $replacement = "\n        " . $newFields . "\n    ";
                }

                $contents = preg_replace(
                    '/\$fillable\s*=\s*\[(.*?)\]/s',
                    '$fillable = [' . $replacement . ']',
                    $contents
                );
                $modified = true;
                $this->line("  <fg=green>✓</> Added 2FA fields to \$fillable: " . implode(', ', $fieldsAdded));
            } else {
                $this->line("  <fg=yellow>!</> Could not find \$fillable array - please add 2FA fields manually");
                $this->line("      Fields needed: " . implode(', ', $fieldsAdded));
            }
        } else {
            $this->line("  <fg=blue>→</> 2FA fields already in \$fillable");
        }

        if ($modified) {
            File::put($path, $contents);
            $this->line("  <fg=green>✓</> User model updated");
        }
    }

    protected function setupBuildoraDirectory(): void
    {
        $resourceDir = app_path('Buildora/Resources');

        if (!File::exists($resourceDir)) {
            File::makeDirectory($resourceDir, 0755, true);
            $this->line("  <fg=green>✓</> Created app/Buildora/Resources directory");
        } else {
            $this->line("  <fg=blue>→</> Buildora directory already exists");
        }
    }

    protected function generateResources(): void
    {
        if ($this->option('skip-resources')) {
            $this->line("  <fg=yellow>!</> Skipping resource generation (--skip-resources)");
            return;
        }

        // Generate User resource
        $userResourcePath = app_path('Buildora/Resources/UserBuildora.php');
        if (!File::exists($userResourcePath) || $this->option('fresh')) {
            Artisan::call('buildora:resource', ['name' => 'user']);
            $this->line("  <fg=green>✓</> Created UserBuildora resource");
        } else {
            $this->line("  <fg=blue>→</> UserBuildora already exists");
        }

        // Generate Permission resource
        $permResourcePath = app_path('Buildora/Resources/PermissionBuildora.php');
        if (!File::exists($permResourcePath) || $this->option('fresh')) {
            Artisan::call('buildora:make-permission-resource');
            $this->line("  <fg=green>✓</> Created PermissionBuildora resource");
        } else {
            $this->line("  <fg=blue>→</> PermissionBuildora already exists");
        }

        // Generate resources for other models
        $modelsPath = app_path('Models');
        if (File::exists($modelsPath)) {
            $files = File::allFiles($modelsPath);
            $skipped = ['User', 'Permission'];

            foreach ($files as $file) {
                $modelName = $file->getFilenameWithoutExtension();

                if (in_array($modelName, $skipped)) {
                    continue;
                }

                $resourcePath = app_path("Buildora/Resources/{$modelName}Buildora.php");

                if (!File::exists($resourcePath) || $this->option('fresh')) {
                    Artisan::call('buildora:resource', ['name' => Str::kebab($modelName)]);
                    $this->line("  <fg=green>✓</> Created {$modelName}Buildora resource");
                } else {
                    $this->line("  <fg=blue>→</> {$modelName}Buildora already exists");
                }
            }
        }

        // Dump autoload to register new classes
        $this->line("  Updating autoloader...");
        exec('composer dump-autoload -q 2>&1');
        $this->line("  <fg=green>✓</> Autoloader updated");
    }

    protected function generatePermissions(): void
    {
        $this->line("  Generating permissions for resources...");

        Artisan::call('buildora:sync-permissions');

        $output = trim(Artisan::output());
        $count = substr_count($output, '✓');

        $this->line("  <fg=green>✓</> Generated {$count} permissions");
    }

    protected function createAdminUser(): void
    {
        if ($this->option('skip-user')) {
            $this->line("  <fg=yellow>!</> Skipping admin user creation (--skip-user)");
            return;
        }

        $this->newLine();

        // Check if any users exist
        $existingUsers = User::count();

        if ($existingUsers > 0) {
            $this->line("  <fg=yellow>!</> {$existingUsers} user(s) already exist in the database");

            if (!$this->confirm('  Create a new admin user anyway?', true)) {
                // Offer to grant permissions to existing user
                if ($this->confirm('  Grant admin permissions to an existing user?', true)) {
                    $email = $this->ask('  Enter the user\'s email address');
                    $user = User::where('email', $email)->first();

                    if ($user) {
                        $this->grantAllPermissions($user);
                        $this->line("  <fg=green>✓</> Admin permissions granted to {$user->email}");
                        return;
                    } else {
                        $this->line("  <fg=red>✗</> User not found with email: {$email}");
                    }
                }
                return;
            }
        }

        $this->line("  Please enter the admin user details:");
        $this->newLine();

        $name = $this->ask('  Name');
        $email = $this->ask('  Email');

        // Validate email
        while (User::where('email', $email)->exists()) {
            $this->error("  A user with this email already exists.");
            $email = $this->ask('  Email');
        }

        $password = $this->secret('  Password');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->line("  <fg=green>✓</> Admin user created (ID: {$user->id})");

        // Grant all permissions
        $this->grantAllPermissions($user);
        $this->line("  <fg=green>✓</> All permissions granted to admin user");
    }

    protected function grantAllPermissions($user): void
    {
        $permissions = \Spatie\Permission\Models\Permission::all();

        foreach ($permissions as $permission) {
            if (!$user->hasPermissionTo($permission)) {
                $user->givePermissionTo($permission);
            }
        }
    }

    protected function finalizeInstallation(): void
    {
        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $this->line("  <fg=green>✓</> Caches cleared");

        // Show route info
        $prefix = config('buildora.route_prefix', 'buildora');
        $this->line("  <fg=green>✓</> Buildora is accessible at: <info>/{$prefix}</info>");
    }

    protected function showSuccess(): void
    {
        $prefix = config('buildora.route_prefix', 'buildora');
        $url = url($prefix);

        $this->newLine(2);
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                              ║');
        $this->line('║   <fg=green>✓ Buildora installed successfully!</>                         ║');
        $this->line('║                                                              ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->info('  Installed components:');
        foreach ($this->installedComponents as $component) {
            $this->line("    • {$component}");
        }

        $this->newLine();
        $this->info('  Next steps:');
        $this->line("    1. Visit <info>{$url}</info> to access Buildora");
        $this->line("    2. Login with your admin credentials");
        $this->line("    3. Customize your resources in <info>app/Buildora/Resources/</info>");
        $this->newLine();

        $this->info('  Useful commands:');
        $this->line("    • <info>php artisan buildora:resource {name}</info> - Create new resource");
        $this->line("    • <info>php artisan buildora:sync-permissions</info> - Sync permissions");
        $this->line("    • <info>php artisan buildora:user:create</info> - Create new user");
        $this->newLine();

        $this->line('  Documentation: <info>https://buildora.dev/docs</info>');
        $this->newLine();
    }

    protected function getBuildoraVersion(): string
    {
        $path = dirname(__DIR__, 2) . '/composer.json';

        if (file_exists($path)) {
            $composer = json_decode(file_get_contents($path), true);
            return $composer['extra']['buildora-version'] ?? $composer['version'] ?? 'dev';
        }

        return 'dev';
    }
}
