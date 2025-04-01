<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BuildoraInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildora:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Buildora and publish its assets, config and dependencies';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Publishing Buildora config and assets...');

        Artisan::call('vendor:publish', [
            '--provider' => 'Ginkelsoft\\Buildora\\Providers\\BuildoraServiceProvider',
            '--tag' => 'buildora-config',
        ]);
        $this->line(Artisan::output());

        Artisan::call('vendor:publish', [
            '--provider' => 'Ginkelsoft\\Buildora\\Providers\\BuildoraServiceProvider',
            '--tag' => 'buildora-assets',
        ]);
        $this->line(Artisan::output());

        $this->info('Ensuring AlpineJS and required NPM packages are installed...');

        $this->ensureNpmDependency('alpinejs');

        $this->info('Running npm install...');
        shell_exec('npm install');

        $this->info('Buildora installation completed.');
    }

    /**
     * Ensure the package is listed in package.json dependencies.
     */
    protected function ensureNpmDependency(string $package): void
    {
        $packageJsonPath = base_path('package.json');

        if (!File::exists($packageJsonPath)) {
            $this->warn('No package.json found. Skipping NPM dependency check.');
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);

        $dependencies = $packageJson['dependencies'] ?? [];

        if (!array_key_exists($package, $dependencies)) {
            $this->info("Adding {$package} to package.json...");
            $packageJson['dependencies'][$package] = '^3.13.0'; // voorbeeldversie
            File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("{$package} is already present in package.json.");
        }
    }
}
