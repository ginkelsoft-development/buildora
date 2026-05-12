<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TextFieldTest extends TestCase
{
    #[Test]
    public function it_can_create_a_text_field(): void
    {
        $field = TextField::make('name', 'Full Name');

        $this->assertInstanceOf(TextField::class, $field);
        $this->assertSame('name', $field->name);
        $this->assertSame('Full Name', $field->label);
        $this->assertSame('text', $field->type);
    }

    #[Test]
    public function it_can_mark_field_as_sortable(): void
    {
        $field = TextField::make('name')->sortable();

        $this->assertTrue($field->sortable);
    }

    #[Test]
    public function it_can_mark_field_as_readonly(): void
    {
        $field = TextField::make('name')->readonly();

        $this->assertTrue($field->readonly);
    }

    #[Test]
    public function it_can_set_help_text(): void
    {
        $field = TextField::make('name')->help('Enter your full name');

        $this->assertSame('Enter your full name', $field->getHelpText());
    }

    #[Test]
    public function it_generates_label_from_field_name_when_not_provided(): void
    {
        $field = TextField::make('name');

        // The label is derived from the field name: "name" -> "Name"
        $this->assertSame('Name', $field->label);
    }
}
