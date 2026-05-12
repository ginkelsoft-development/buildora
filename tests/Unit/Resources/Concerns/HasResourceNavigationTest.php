<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources\Concerns;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceNavigation;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

class RNCustomer extends Model
{
    use HasBuildora;
    protected $table = 'rn_customers';
    protected $guarded = [];
    public $timestamps = false;
}

class CustomerBuildora extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RNCustomer::class;
    }

    public function defineFields(): array
    {
        return [];
    }
}

class HiddenFromNavigationResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RNCustomer::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function showInNavigation(): bool
    {
        return false;
    }
}

class HasResourceNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('rn_customers', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
        });
    }

    #[Test]
    public function default_title_derives_from_the_model_class_basename(): void
    {
        $resource = new CustomerBuildora();

        $this->assertSame('RNCustomer', $resource->title());
    }

    #[Test]
    public function default_show_in_navigation_is_true(): void
    {
        $resource = new CustomerBuildora();

        $this->assertTrue($resource->showInNavigation());
    }

    #[Test]
    public function subclass_can_opt_out_of_navigation(): void
    {
        $resource = new HiddenFromNavigationResource();

        $this->assertFalse($resource->showInNavigation());
    }

    #[Test]
    public function slug_strips_the_buildora_suffix_and_lowercases(): void
    {
        $this->assertSame('customerbuildora', strtolower('CustomerBuildora'));
        // CustomerBuildora -> 'customer' after str_replace('buildora', '', lower)
        $this->assertSame('customer', CustomerBuildora::slug());
    }

    #[Test]
    public function default_search_result_config_has_label_and_columns_keys(): void
    {
        $config = (new CustomerBuildora())->searchResultConfig();

        $this->assertArrayHasKey('label', $config);
        $this->assertArrayHasKey('columns', $config);
        $this->assertIsArray($config['columns']);
    }

    #[Test]
    public function buildora_resource_uses_the_navigation_trait(): void
    {
        $traits = (new ReflectionClass(BuildoraResource::class))->getTraitNames();

        $this->assertContains(HasResourceNavigation::class, $traits);
    }

    #[Test]
    public function navigation_methods_are_no_longer_inlined_in_buildora_resource(): void
    {
        $resourceSource = file_get_contents(
            (new ReflectionClass(BuildoraResource::class))->getFileName()
        );

        foreach (['title', 'searchResultConfig', 'showInNavigation', 'slug'] as $movedMethod) {
            $this->assertStringNotContainsString(
                "function {$movedMethod}(",
                $resourceSource,
                "Method '{$movedMethod}' has been re-inlined into BuildoraResource.php — it should live in HasResourceNavigation trait."
            );
        }

        $traitSource = file_get_contents(
            (new ReflectionClass(HasResourceNavigation::class))->getFileName()
        );

        foreach (['title', 'searchResultConfig', 'showInNavigation', 'slug'] as $movedMethod) {
            $this->assertStringContainsString(
                "function {$movedMethod}(",
                $traitSource,
                "Method '{$movedMethod}' must be declared in HasResourceNavigation."
            );
        }
    }
}
