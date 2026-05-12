<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources\Concerns;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceFields;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

class RFItem extends Model
{
    use HasBuildora;
    protected $table = 'rf_items';
    protected $guarded = [];
    public $timestamps = false;
}

class RFItemBuildora extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RFItem::class;
    }

    public function defineFields(): array
    {
        return [
            TextField::make('title', 'Titel'),
        ];
    }
}

class HasResourceFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('rf_items', function ($t) {
            $t->increments('id');
            $t->string('title')->nullable();
        });
    }

    #[Test]
    public function get_fields_returns_the_resource_field_collection(): void
    {
        $resource = new RFItemBuildora();

        $fields = $resource->getFields();

        $this->assertCount(1, $fields);
        $this->assertSame('title', $fields[0]->name);
    }

    #[Test]
    public function fill_sets_the_field_value_from_the_model_attribute(): void
    {
        $item = RFItem::create(['title' => 'Hallo']);
        $resource = new RFItemBuildora();

        $resource->fill($item);

        $fields = $resource->getFields();
        $this->assertSame('Hallo', $fields[0]->value);
    }

    #[Test]
    public function set_fields_rejects_non_field_entries(): void
    {
        $resource = new RFItemBuildora();

        $this->expectException(BuildoraException::class);
        $resource->setFields(['not a field']);
    }

    #[Test]
    public function set_fields_accepts_a_valid_field_array(): void
    {
        $resource = new RFItemBuildora();

        $resource->setFields([TextField::make('alt')]);

        $this->assertSame('alt', $resource->getFields()[0]->name);
    }

    #[Test]
    public function resolve_fields_re_prepares_fields_for_a_given_model(): void
    {
        $item = RFItem::create(['title' => 'Resolved']);
        $resource = new RFItemBuildora();

        $resolved = $resource->resolveFields($item);

        $this->assertCount(1, $resolved);
        $this->assertSame('Resolved', $resolved[0]->value);
    }

    #[Test]
    public function buildora_resource_uses_the_fields_trait(): void
    {
        $traits = (new ReflectionClass(BuildoraResource::class))->getTraitNames();

        $this->assertContains(HasResourceFields::class, $traits);
    }

    #[Test]
    public function field_methods_are_no_longer_inlined_in_buildora_resource(): void
    {
        $resourceSource = file_get_contents(
            (new ReflectionClass(BuildoraResource::class))->getFileName()
        );

        // defineFields stays abstract on BuildoraResource itself, so it is
        // *not* part of the extraction check.
        foreach (['fill', 'setFields', 'getFields', 'resolveFields'] as $movedMethod) {
            $this->assertStringNotContainsString(
                "public function {$movedMethod}(",
                $resourceSource,
                "Method '{$movedMethod}' has been re-inlined into BuildoraResource.php — it should live in HasResourceFields trait."
            );
        }

        $traitSource = file_get_contents(
            (new ReflectionClass(HasResourceFields::class))->getFileName()
        );

        foreach (['fill', 'setFields', 'getFields', 'resolveFields'] as $movedMethod) {
            $this->assertStringContainsString(
                "function {$movedMethod}(",
                $traitSource,
            );
        }
    }
}
