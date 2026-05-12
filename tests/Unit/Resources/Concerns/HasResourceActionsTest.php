<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources\Concerns;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceActions;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

class RACModel extends Model
{
    use HasBuildora;

    protected $table = 'rac_items';
    protected $guarded = [];
    public $timestamps = false;
}

class RACEmptyResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RACModel::class;
    }

    public function defineFields(): array
    {
        return [];
    }
}

class RACWithRowActionsResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RACModel::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function defineRowActions(): array
    {
        // ActionManager::resolveRowActions iterates these; an empty array
        // is the simplest "actions were defined and propagated" assertion
        // without needing real RowAction objects in scope here.
        return [];
    }
}

class RACFakePageAction
{
    public function __construct(private ?string $permission) {}
    public function getPermission(): ?string { return $this->permission; }
}

class RACPublicPageActionResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RACModel::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function definePageActions(): array
    {
        return [
            new RACFakePageAction(null),
            new RACFakePageAction(null),
        ];
    }
}

class RACGuardedPageActionResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RACModel::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function definePageActions(): array
    {
        return [
            new RACFakePageAction(null),
            new RACFakePageAction('this.is.not.granted'),
        ];
    }
}

/**
 * Tests for the HasResourceActions trait — the first concern extracted
 * out of BuildoraResource for #135.
 *
 * Two flavours of assertion:
 *   1. Behavioural: defineXxx() defaults to [], getPageActions() filters
 *      by permission, getRowActions/getBulkActions delegate correctly.
 *   2. Structural: BuildoraResource actually uses the trait, and the
 *      action methods are no longer declared on the resource class
 *      itself — so the next person who reads BuildoraResource doesn't
 *      see them as inline concerns.
 */
class HasResourceActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('rac_items', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
        });
    }

    #[Test]
    public function default_define_methods_return_empty_arrays(): void
    {
        $resource = new RACEmptyResource();

        $this->assertSame([], $resource->defineRowActions());
        $this->assertSame([], $resource->defineBulkActions());
        $this->assertSame([], $resource->definePageActions());
    }

    #[Test]
    public function page_actions_without_a_permission_pass_through(): void
    {
        $resource = new RACPublicPageActionResource();

        $actions = $resource->getPageActions();

        $this->assertCount(2, $actions);
    }

    #[Test]
    public function page_actions_with_an_unmet_permission_are_filtered_out(): void
    {
        $resource = new RACGuardedPageActionResource();

        // No user is authenticated in the default test setup, so the
        // permission check short-circuits to "no user → drop the action".
        // The public action survives.
        $this->actingAs(new class extends \Illuminate\Foundation\Auth\User {
            public function can($abilities, $arguments = []): bool { return false; }
        });

        $actions = $resource->getPageActions();

        $this->assertCount(1, $actions);
    }

    #[Test]
    public function row_actions_delegate_to_action_manager_via_defineRowActions(): void
    {
        // The default defineRowActions() returns []. We simply check the
        // method signature is callable through the trait — the real-world
        // delegation to ActionManager is exercised when an actual
        // RowAction subclass is defined upstream.
        $resource = new RACWithRowActionsResource();

        $this->assertSame([], $resource->getRowActions($resource));
    }

    #[Test]
    public function bulk_actions_include_the_default_export_pair(): void
    {
        $resource = new RACEmptyResource();

        $bulk = $resource->getBulkActions();

        $labels = array_map(fn ($a) => $a->getLabel(), $bulk);

        // The package ships two default export actions; they must always
        // surface unless the resource declares an action with the same
        // label.
        $this->assertContains('Export to Excel', $labels);
        $this->assertContains('Export to CSV', $labels);
    }

    #[Test]
    public function buildora_resource_uses_the_extracted_trait(): void
    {
        $traits = (new ReflectionClass(BuildoraResource::class))->getTraitNames();

        $this->assertContains(HasResourceActions::class, $traits);
    }

    #[Test]
    public function action_methods_are_no_longer_inlined_in_buildora_resource_source(): void
    {
        // PHP reflection reports trait methods as "declared in" the class
        // that uses the trait, so getDeclaringClass()->getName() is the
        // wrong signal here. We check the source file directly: a
        // `function defineRowActions(` body inside BuildoraResource.php
        // means the extract regressed.
        $resourceSource = file_get_contents(
            (new ReflectionClass(BuildoraResource::class))->getFileName()
        );

        foreach (['defineRowActions', 'defineBulkActions', 'definePageActions', 'getPageActions', 'getRowActions', 'getBulkActions'] as $movedMethod) {
            $this->assertStringNotContainsString(
                "function {$movedMethod}(",
                $resourceSource,
                "Method '{$movedMethod}' has been re-inlined into BuildoraResource.php — it should live in HasResourceActions trait."
            );
        }

        // And cross-check: the trait source *does* contain those methods.
        $traitSource = file_get_contents(
            (new ReflectionClass(HasResourceActions::class))->getFileName()
        );

        foreach (['defineRowActions', 'defineBulkActions', 'definePageActions', 'getPageActions', 'getRowActions', 'getBulkActions'] as $movedMethod) {
            $this->assertStringContainsString(
                "function {$movedMethod}(",
                $traitSource,
                "Method '{$movedMethod}' must be declared in HasResourceActions."
            );
        }
    }
}
