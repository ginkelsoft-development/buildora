<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function index()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect()->route('buildora.login');
        }

        $requirements = $this->checkRequirements();
        $version = $this->getBuildoraVersion();

        return view('buildora::install.index', compact('requirements', 'version'));
    }

    public function checkRequirements(): array
    {
        return [
            'php_version' => [
                'label' => 'PHP >= 8.1',
                'passed' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'current' => PHP_VERSION,
            ],
            'laravel_version' => [
                'label' => 'Laravel >= 10.0',
                'passed' => version_compare(app()->version(), '10.0.0', '>='),
                'current' => app()->version(),
            ],
            'database' => [
                'label' => 'Database connection',
                'passed' => $this->checkDatabaseConnection(),
                'current' => $this->checkDatabaseConnection() ? 'Connected' : 'Not connected',
            ],
            'spatie_permission' => [
                'label' => 'Spatie Permission package',
                'passed' => class_exists(\Spatie\Permission\PermissionServiceProvider::class),
                'current' => class_exists(\Spatie\Permission\PermissionServiceProvider::class) ? 'Installed' : 'Not installed',
            ],
            'livewire' => [
                'label' => 'Livewire package',
                'passed' => class_exists(\Livewire\Livewire::class),
                'current' => class_exists(\Livewire\Livewire::class) ? 'Installed' : 'Not installed',
            ],
            'user_model' => [
                'label' => 'User model exists',
                'passed' => class_exists(\App\Models\User::class),
                'current' => class_exists(\App\Models\User::class) ? 'Found' : 'Not found',
            ],
            'writable_config' => [
                'label' => 'Config directory writable',
                'passed' => is_writable(config_path()),
                'current' => is_writable(config_path()) ? 'Writable' : 'Not writable',
            ],
            'writable_app' => [
                'label' => 'App directory writable',
                'passed' => is_writable(app_path()),
                'current' => is_writable(app_path()) ? 'Writable' : 'Not writable',
            ],
        ];
    }

    protected function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function process(Request $request)
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $steps = [];

        try {
            // Step 1: Publish config
            $steps[] = $this->publishConfig();

            // Step 2: Run migrations
            $steps[] = $this->runMigrations();

            // Step 3: Setup User model
            $steps[] = $this->setupUserModel();

            // Step 4: Setup Buildora directory
            $steps[] = $this->setupBuildoraDirectory();

            // Step 5: Generate resources
            $steps[] = $this->generateResources();

            // Step 6: Generate permissions
            $steps[] = $this->generatePermissions();

            // Step 7: Create admin user
            $steps[] = $this->createAdminUser(
                $request->admin_name,
                $request->admin_email,
                $request->admin_password
            );

            // Step 8: Finalize
            $steps[] = $this->finalizeInstallation();

            // Mark as installed
            $this->markAsInstalled();

            return response()->json([
                'success' => true,
                'steps' => $steps,
                'redirect' => route('buildora.login'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Buildora installation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'steps_completed' => count($steps),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'steps' => $steps,
            ], 500);
        }
    }

    protected function publishConfig(): array
    {
        try {
            $configPath = config_path('buildora.php');

            if (!File::exists($configPath)) {
                Artisan::call('vendor:publish', [
                    '--tag' => 'buildora-config',
                    '--force' => true,
                ]);
            }

            return [
                'step' => 'Publishing configuration',
                'success' => true,
                'message' => 'Configuration published successfully',
            ];
        } catch (\Exception $e) {
            throw new \Exception('Config publish failed: ' . $e->getMessage());
        }
    }

    protected function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return [
                'step' => 'Running migrations',
                'success' => true,
                'message' => 'Database migrations completed',
            ];
        } catch (\Exception $e) {
            throw new \Exception('Migration failed: ' . $e->getMessage());
        }
    }

    protected function setupUserModel(): array
    {
        $path = app_path('Models/User.php');
        $messages = [];

        if (!File::exists($path)) {
            throw new \Exception('User model not found');
        }

        $contents = File::get($path);
        $modified = false;

        if (!Str::contains($contents, 'HasRoles')) {
            $contents = Str::replaceFirst(
                '{',
                "{\n    use \\Spatie\\Permission\\Traits\\HasRoles;",
                $contents
            );
            $modified = true;
            $messages[] = 'Added HasRoles trait';
        }

        if (!Str::contains($contents, 'HasBuildora')) {
            $contents = Str::replaceFirst(
                '{',
                "{\n    use \\Ginkelsoft\\Buildora\\Traits\\HasBuildora;",
                $contents
            );
            $modified = true;
            $messages[] = 'Added HasBuildora trait';
        }

        if ($modified) {
            File::put($path, $contents);
        }

        return [
            'step' => 'Configuring User model',
            'success' => true,
            'message' => $modified ? implode(', ', $messages) : 'User model already configured',
        ];
    }

    protected function setupBuildoraDirectory(): array
    {
        $resourceDir = app_path('Buildora/Resources');

        if (!File::exists($resourceDir)) {
            File::makeDirectory($resourceDir, 0755, true);
        }

        return [
            'step' => 'Setting up Buildora directory',
            'success' => true,
            'message' => 'Directory structure created',
        ];
    }

    protected function generateResources(): array
    {
        $created = [];
        $errors = [];

        // Ensure directory exists
        $resourceDir = app_path('Buildora/Resources');
        if (!File::exists($resourceDir)) {
            File::makeDirectory($resourceDir, 0755, true);
        }

        // User resource - create directly without Artisan command
        try {
            $userResourcePath = app_path('Buildora/Resources/UserBuildora.php');
            if (!File::exists($userResourcePath)) {
                $stub = $this->getResourceStub('User', 'App\\Models\\User');
                File::put($userResourcePath, $stub);
                $created[] = 'UserBuildora';
            }
        } catch (\Exception $e) {
            $errors[] = 'UserBuildora: ' . $e->getMessage();
        }

        // Permission model
        try {
            $permModelPath = app_path('Models/Permission.php');
            if (!File::exists($permModelPath)) {
                File::put($permModelPath, $this->getPermissionModelStub());
                $created[] = 'Permission model';
            }
        } catch (\Exception $e) {
            $errors[] = 'Permission model: ' . $e->getMessage();
        }

        // Permission resource
        try {
            $permResourcePath = app_path('Buildora/Resources/PermissionBuildora.php');
            if (!File::exists($permResourcePath)) {
                File::put($permResourcePath, $this->getPermissionResourceStub());
                $created[] = 'PermissionBuildora';
            }
        } catch (\Exception $e) {
            $errors[] = 'PermissionBuildora: ' . $e->getMessage();
        }

        // Other models
        $modelsPath = app_path('Models');
        if (File::exists($modelsPath)) {
            $files = File::allFiles($modelsPath);
            $skipped = ['User', 'Permission'];

            foreach ($files as $file) {
                $modelName = $file->getFilenameWithoutExtension();

                if (in_array($modelName, $skipped)) {
                    continue;
                }

                try {
                    $resourcePath = app_path("Buildora/Resources/{$modelName}Buildora.php");

                    if (!File::exists($resourcePath)) {
                        $modelClass = 'App\\Models\\' . $modelName;
                        $stub = $this->getResourceStub($modelName, $modelClass);
                        File::put($resourcePath, $stub);
                        $created[] = "{$modelName}Buildora";
                    }
                } catch (\Exception $e) {
                    $errors[] = "{$modelName}Buildora: " . $e->getMessage();
                }
            }
        }

        // Dump autoload
        exec('composer dump-autoload -q 2>&1');

        $message = '';
        if (count($created) > 0) {
            $message = 'Created: ' . implode(', ', $created);
        } else {
            $message = 'Resources already exist';
        }

        if (count($errors) > 0) {
            $message .= ' | Errors: ' . implode('; ', $errors);
        }

        return [
            'step' => 'Generating resources',
            'success' => count($errors) === 0,
            'message' => $message,
        ];
    }

    protected function generatePermissions(): array
    {
        try {
            // Create core permissions directly without relying on Artisan command
            $permissions = [
                // User permissions
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',
                // Permission permissions
                'permission.view',
                'permission.create',
                'permission.edit',
                'permission.delete',
                // Dashboard permissions
                'dashboard.view',
            ];

            $created = [];
            foreach ($permissions as $permissionName) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
                $created[] = $permissionName;
            }

            return [
                'step' => 'Generating permissions',
                'success' => true,
                'message' => 'Created ' . count($created) . ' permissions',
            ];
        } catch (\Exception $e) {
            throw new \Exception('Permission creation failed: ' . $e->getMessage());
        }
    }

    protected function createAdminUser(string $name, string $email, string $password): array
    {
        $userClass = config('buildora.user_model', \App\Models\User::class);

        // Check if user already exists
        if ($userClass::where('email', $email)->exists()) {
            $user = $userClass::where('email', $email)->first();
        } else {
            $user = $userClass::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);
        }

        // Grant all permissions
        $permissions = \Spatie\Permission\Models\Permission::all();
        foreach ($permissions as $permission) {
            if (!$user->hasPermissionTo($permission)) {
                $user->givePermissionTo($permission);
            }
        }

        return [
            'step' => 'Creating admin user',
            'success' => true,
            'message' => "Admin user '{$email}' created with all permissions",
        ];
    }

    protected function finalizeInstallation(): array
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return [
            'step' => 'Finalizing installation',
            'success' => true,
            'message' => 'Caches cleared, installation complete',
        ];
    }

    protected function isInstalled(): bool
    {
        // Check 1: Lock file exists (fastest check)
        $lockFile = storage_path('buildora_installed');
        if (File::exists($lockFile)) {
            return true;
        }

        // Check 2: Resources directory exists with Buildora resource files
        $resourceDir = app_path('Buildora/Resources');
        if (!File::exists($resourceDir)) {
            return false;
        }

        $resourceFiles = File::files($resourceDir);
        $hasResources = count($resourceFiles) > 0;

        if (!$hasResources) {
            return false;
        }

        // Check 3: Config file is published
        $hasConfig = File::exists(config_path('buildora.php'));

        // Check 4: Users table exists and has at least one user
        $hasUsers = false;
        try {
            if (Schema::hasTable('users')) {
                $userClass = config('buildora.user_model', \App\Models\User::class);
                if (class_exists($userClass)) {
                    $hasUsers = $userClass::count() > 0;
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        // Check 5: Permissions table exists (Spatie installed and migrated)
        $hasPermissions = false;
        try {
            $hasPermissions = Schema::hasTable('permissions');
        } catch (\Exception $e) {
            // Silently fail
        }

        // If we have resources + users + permissions table, consider it installed
        // This covers existing installations that don't have the lock file
        if ($hasResources && $hasUsers && $hasPermissions) {
            // Create the lock file for future checks (faster)
            $this->markAsInstalled();
            return true;
        }

        // If we have resources + config + permissions, it's installed (maybe no users yet)
        if ($hasResources && $hasConfig && $hasPermissions) {
            return true;
        }

        return false;
    }

    protected function markAsInstalled(): void
    {
        File::put(storage_path('buildora_installed'), now()->toDateTimeString());
    }

    protected function getBuildoraVersion(): string
    {
        $path = dirname(__DIR__, 3) . '/composer.json';

        if (file_exists($path)) {
            $composer = json_decode(file_get_contents($path), true);
            return $composer['extra']['buildora-version'] ?? $composer['version'] ?? 'dev';
        }

        return 'dev';
    }

    protected function getResourceStub(string $modelName, string $modelClass): string
    {
        $resourceName = $modelName . 'Buildora';

        // Special case for User model
        if ($modelName === 'User') {
            return $this->getUserResourceStub();
        }

        return <<<PHP
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\DateTimeField;
use {$modelClass};

class {$resourceName} extends BuildoraResource
{
    protected static string \$model = {$modelName}::class;

    public function defineFields(): array
    {
        return [
            IDField::make('id')->sortable(),
            TextField::make('name')->sortable()->searchable(),
            DateTimeField::make('created_at')->sortable()->hideOnForms(),
            DateTimeField::make('updated_at')->sortable()->hideOnForms(),
        ];
    }
}
PHP;
    }

    protected function getUserResourceStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\DateTimeField;
use Ginkelsoft\Buildora\Fields\Types\CheckboxListField;
use Ginkelsoft\Buildora\Actions\RowAction;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserBuildora extends BuildoraResource
{
    protected static string $model = User::class;

    /**
     * Define the title/label for this resource.
     */
    public function title(): string
    {
        return __buildora('Users');
    }

    /**
     * Configure search result display.
     */
    public function searchResultConfig(): array
    {
        return [
            'label' => 'name',
            'columns' => ['name', 'email'],
        ];
    }

    /**
     * Define the fields for this resource.
     */
    public function defineFields(): array
    {
        return [
            IDField::make('id', __buildora('ID'))
                ->readonly()
                ->hideFromTable()
                ->hideFromExport()
                ->hideFromCreate()
                ->hideFromEdit()
                ->hideFromDetail(),

            TextField::make('name', __buildora('Name'))
                ->sortable()
                ->searchable()
                ->validation(['required', 'string', 'max:255'])
                ->columnSpan(['default' => 12, 'lg' => 6]),

            EmailField::make('email', __buildora('Email'))
                ->sortable()
                ->searchable()
                ->validation(['required', 'email', 'max:255'])
                ->columnSpan(['default' => 12, 'lg' => 6]),

            PasswordField::make('password', __buildora('Password'))
                ->hideFromTable()
                ->hideFromDetail()
                ->hideFromExport()
                ->validation(['nullable', 'min:8'])
                ->columnSpan(['default' => 12, 'lg' => 6]),

            CheckboxListField::make('permissions', __buildora('Permissions'))
                ->relatedTo(Permission::class)
                ->pluck('id', 'name')
                ->groupByPrefix(true, '.')
                ->hideFromTable()
                ->columnSpan(['default' => 12])
                ->help(__buildora('Select the permissions for this user.')),

            DateTimeField::make('created_at', __buildora('Created at'))
                ->sortable()
                ->hideFromCreate()
                ->hideFromEdit(),

            DateTimeField::make('updated_at', __buildora('Updated at'))
                ->sortable()
                ->hideFromCreate()
                ->hideFromEdit(),
        ];
    }

    /**
     * Define row actions for individual records.
     */
    public function defineRowActions(): array
    {
        return [
            RowAction::make(__buildora('View'), 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id']),

            RowAction::make(__buildora('Edit'), 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('user.edit'),

            RowAction::make(__buildora('Delete'), 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['resource' => 'user', 'id' => 'id'])
                ->permission('user.delete')
                ->confirm(__buildora('Are you sure you want to delete this user?')),
        ];
    }

    /**
     * Define bulk actions for multiple records.
     */
    public function defineBulkActions(): array
    {
        return [];
    }

    /**
     * Define page-level actions.
     */
    public function definePageActions(): array
    {
        return [];
    }

    /**
     * Define relation panels for the detail view.
     */
    public function definePanels(): array
    {
        return [];
    }

    /**
     * Define widgets for this resource.
     */
    public function defineWidgets(): array
    {
        return [];
    }

    /**
     * Show this resource in the navigation menu.
     */
    public function showInNavigation(): bool
    {
        return true;
    }
}
PHP;
    }

    protected function getPermissionModelStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name'];

    protected $appends = ['label'];

    /**
     * Get the human-readable label for the permission.
     * Converts "user.edit" to "User Edit".
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn () => ucwords(str_replace(['.', '_', '-'], ' ', $this->name))
        );
    }
}
PHP;
    }

    protected function getPermissionResourceStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\DateTimeField;
use Ginkelsoft\Buildora\Actions\RowAction;
use App\Models\Permission;

class PermissionBuildora extends BuildoraResource
{
    protected static string $model = Permission::class;

    /**
     * Define the title/label for this resource.
     */
    public function title(): string
    {
        return __buildora('Permissions');
    }

    /**
     * Configure search result display.
     */
    public function searchResultConfig(): array
    {
        return [
            'label' => 'name',
            'columns' => ['name', 'guard_name'],
        ];
    }

    /**
     * Define the fields for this resource.
     */
    public function defineFields(): array
    {
        return [
            IDField::make('id', __buildora('ID'))
                ->readonly()
                ->hideFromTable()
                ->hideFromExport()
                ->hideFromCreate()
                ->hideFromEdit()
                ->hideFromDetail(),

            TextField::make('label', __buildora('Label'))
                ->sortable()
                ->searchable()
                ->hideFromCreate()
                ->hideFromEdit(),

            TextField::make('name', __buildora('Name'))
                ->sortable()
                ->searchable()
                ->validation(['required', 'string', 'max:255'])
                ->columnSpan(['default' => 12, 'lg' => 6]),

            TextField::make('guard_name', __buildora('Guard'))
                ->sortable()
                ->columnSpan(['default' => 12, 'lg' => 6]),

            DateTimeField::make('created_at', __buildora('Created at'))
                ->sortable()
                ->hideFromCreate()
                ->hideFromEdit(),

            DateTimeField::make('updated_at', __buildora('Updated at'))
                ->sortable()
                ->hideFromCreate()
                ->hideFromEdit(),
        ];
    }

    /**
     * Define row actions for individual records.
     */
    public function defineRowActions(): array
    {
        return [
            RowAction::make(__buildora('View'), 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id']),

            RowAction::make(__buildora('Edit'), 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.edit'),

            RowAction::make(__buildora('Delete'), 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['resource' => 'permission', 'id' => 'id'])
                ->permission('permission.delete')
                ->confirm(__buildora('Are you sure you want to delete this permission?')),
        ];
    }

    /**
     * Define bulk actions for multiple records.
     */
    public function defineBulkActions(): array
    {
        return [];
    }

    /**
     * Define page-level actions.
     */
    public function definePageActions(): array
    {
        return [];
    }

    /**
     * Define relation panels for the detail view.
     */
    public function definePanels(): array
    {
        return [];
    }

    /**
     * Define widgets for this resource.
     */
    public function defineWidgets(): array
    {
        return [];
    }

    /**
     * Show this resource in the navigation menu.
     */
    public function showInNavigation(): bool
    {
        return true;
    }
}
PHP;
    }
}
