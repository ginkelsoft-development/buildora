<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\HasOneField;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class HasOneFieldTest extends TestCase
{
    #[Test]
    public function make_returns_a_hasone_field_instance(): void
    {
        $field = HasOneField::make('profile');

        // Before #142 this asserted as Field but not HasOneField, because
        // Field::make() uses `new self()` and HasOneField had no override.
        $this->assertInstanceOf(Field::class, $field);
        $this->assertInstanceOf(HasOneField::class, $field);
    }

    #[Test]
    public function make_keeps_relation_type_label(): void
    {
        $field = HasOneField::make('profile');

        $this->assertSame('profile', $field->name);
        $this->assertSame('hasOne', $field->type);
        $this->assertSame('Profile', $field->label);
    }

    #[Test]
    public function make_accepts_an_explicit_label(): void
    {
        $field = HasOneField::make('profile', 'User Profile');

        $this->assertSame('User Profile', $field->label);
    }

    #[Test]
    public function relatedTo_returns_a_hasone_field_so_chains_keep_working(): void
    {
        $field = HasOneField::make('profile')->relatedTo(\stdClass::class);

        // Builder chain must keep working — i.e. the type-specific method
        // relatedTo returns an instance of HasOneField, not a base Field.
        $this->assertInstanceOf(HasOneField::class, $field);
    }
}
