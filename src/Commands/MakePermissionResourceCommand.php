<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakePermissionResourceCommand extends Command
{
    protected $signature = 'buildora:make-permission-resource';

    protected $description = 'Generate a Buildora resource and model for managing permissions using Spatie Permissions';

    public function handle(): void
    {
        $resourcePath = app_path('Buildora/Resources/PermissionBuildora.php');
        $modelPath = app_path('Models/Permission.php');

        // Generate model if it doesn't exist
        if (!File::exists($modelPath)) {
            File::ensureDirectoryExists(dirname($modelPath));
            File::put($modelPath, $this->getModelStub());
            $this->info('Permission model created successfully.');
        } else {
            $this->warn('Permission model already exists.');
        }

        // Generate resource if it doesn't exist
        if (!File::exists($resourcePath)) {
            File::ensureDirectoryExists(dirname($resourcePath));
            File::put($resourcePath, $this->getResourceStub());
            $this->info('PermissionBuildora resource created successfully.');
        } else {
            $this->warn('PermissionBuildora resource already exists.');
        }
    }

    protected function getModelStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Ginkelsoft\Buildora\Traits\HasBuildora;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasBuildora;
    
    protected $fillable = [
        'name',
        'guard_name',
        'label',
    ];
}
PHP;
    }

    protected function getResourceStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Buildora\Resources;

use App\Models\Permission;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\SelectField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Actions\PageAction;

class PermissionBuildora extends BuildoraResource
{
    protected static string $model = Permission::class;

    public function title(): string
    {
        return 'Permissions';
    }

    public function defineFields(): array
    {
        return [
            IDField::make('id')->readonly()->hideFromTable()->hideFromExport(),

            TextField::make('name', 'Name')->sortable(),

            TextField::make('label', 'Label')->help('This label is used to make the permission readable in the UI.'),

            SelectField::make('guard_name', 'Guard')
                ->options([
                    'web' => 'Web',
                ])
                ->readonly(true),
        ];
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make('View', 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.view'),
                
            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.edit'),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['id' => 'id'])
                ->permission('permission.delete')
                ->confirm('Are you sure you want to delete this permission?'),
        ];
    }

    public function defineBulkActions(): array
    {
        return [];
    }

    public function defineWidgets(): array
    {
        return [];
    }

    public function definePanels(): array
    {
        return [];
    }

    public function definePageActions(): array
    {
        return [
            PageAction::make(
                'Permissions Synchroniseren',
                'fas fa-sync',
                'buildora.permissions.sync'
            )
                ->style('success')
                ->permission('permission.create'),
        ];
    }
}
PHP;
    }
}
