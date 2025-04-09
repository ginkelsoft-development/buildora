<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallBuildoraCommand extends Command
{
    protected $signature = 'buildora:install';
    protected $description = 'Interactive installer for Buildora';

    public function handle(): int
    {
        $this->intro();

        if (! $this->confirmContinueInProduction()) {
            return self::FAILURE;
        }

        $this->detectVersion();
        $this->checkSpatieInstalled();
        $this->checkUserModel();
        $this->runMigrations();
        $this->ensureUserModelTraits();
        $this->generateUserResource();
        $this->generateOtherModelResources();
        $this->generatePermissions();
        $userId = $this->createAdminUser();
        $this->assignPermissions($userId);

        $this->success();
        return self::SUCCESS;
    }

    protected function intro(): void
    {
        $this->line(str_repeat('=', 40));
        $this->line('        Buildora Install Wizard');
        $this->line(str_repeat('=', 40));
    }

    protected function confirmContinueInProduction(): bool
    {
        $this->line("\nStep 1/9: Detecting environment...");
        $env = app()->environment();
        $this->line("→ Environment: $env");

        if ($env === 'production') {
            return $this->confirm('You are running in production. Continue?', false);
        }

        return true;
    }

    protected function detectVersion(): void
    {
        $this->line("\nStep 2/9: Detecting Buildora version...");
        $path = dirname(__DIR__, 2) . '/composer.json';
        if (file_exists($path)) {
            $composer = json_decode(file_get_contents($path), true);
            $version = $composer['extra']['buildora-version'] ?? 'dev';
            config(['buildora.version' => $version]);
            $this->line("→ Version: $version");
        } else {
            $this->warn('Could not read composer.json for version.');
        }
    }

    protected function checkSpatieInstalled(): void
    {
        $this->line("\nStep 3/9: Checking Spatie permission package...");
        if (! class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            $this->error('spatie/laravel-permission not found. Install it before continuing.');
            exit(1);
        }
        $this->line("→ spatie/laravel-permission is installed");
    }

    protected function checkUserModel(): void
    {
        $this->line("\nStep 4/9: Checking if User model exists...");
        if (! class_exists(\App\Models\User::class)) {
            $this->error('User model not found at App\\Models\\User');
            exit(1);
        }
        $this->line("→ Found: App\\Models\\User");
    }

    protected function runMigrations(): void
    {
        $this->line("\nStep 5/9: Running database migrations...");
        Artisan::call('migrate', ['--force' => true]);
        $this->line("→ " . trim(Artisan::output()));
    }

    protected function ensureUserModelTraits(): void
    {
        $this->line("\nStep 6/9: Ensuring traits on User model...");
        $path = app_path('Models/User.php');
        $contents = File::get($path);

        if (! Str::contains($contents, 'HasRoles')) {
            $contents = Str::replaceFirst('{', "{\n    use \\Spatie\\Permission\\Traits\\HasRoles;", $contents);
            $this->line("→ Added HasRoles trait");
        }

        if (! Str::contains($contents, 'HasBuildora')) {
            $contents = Str::replaceFirst('{', "{\n    use \\Ginkelsoft\\Buildora\\Traits\\HasBuildora;", $contents);
            $this->line("→ Added HasBuildora trait");
        }

        File::put($path, $contents);
    }

    protected function generateUserResource(): void
    {
        $this->line("\nStep 7/9: Creating Buildora resource for User...");
        Artisan::call('buildora:resource', ['name' => 'user']);
        $this->line("→ User resource created");
    }

    protected function generateOtherModelResources(): void
    {
        $this->line("\n→ Checking for other Eloquent models...");
        $files = File::allFiles(app_path('Models'));

        foreach ($files as $file) {
            $model = $file->getFilenameWithoutExtension();
            if (Str::lower($model) === 'user') {
                continue;
            }
            $this->line("→ Generating resource for: $model");
            Artisan::call('buildora:resource', ['name' => Str::kebab($model)]);
            $this->line("   ✔ $model");
        }
    }

    protected function generatePermissions(): void
    {
        $this->line("\nStep 8/9: Generating permissions...");
        Artisan::call('buildora:generate-permissions');
        $this->line("→ Permissions generated");
    }

    protected function createAdminUser(): int
    {
        $this->line("\nStep 9/9: Create admin user");
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $user = \App\Models\User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->line("→ User created: ID = {$user->id}");
        return $user->id;
    }

    protected function assignPermissions(int $userId): void
    {
        $this->line("\nGranting permissions to admin user...");
        Artisan::call('buildora:permission:grant-resource', [
            'user_id' => $userId,
            'resource' => 'user',
        ]);
        $this->line("→ Permissions granted for user.*");
    }

    protected function success(): void
    {
        $this->line("\nBuildora installed successfully!");
    }
}
