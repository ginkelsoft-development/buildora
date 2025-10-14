<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Tests\TestCase;

class TextFieldTest extends TestCase
{
    /** @test */
    public function it_can_create_a_text_field(): void
    {
        $field = TextField::make('name', 'Full Name');

        $this->assertInstanceOf(TextField::class, $field);
        $this->assertEquals('name', $field->name);
        $this->assertEquals('Full Name', $field->label);
        $this->assertEquals('text', $field->type);
    }

    /** @test */
    public function it_can_mark_field_as_sortable(): void
    {
        $field = TextField::make('name')->sortable();

        $this->assertTrue($field->sortable);
    }

    /** @test */
    public function it_can_mark_field_as_readonly(): void
    {
        $field = TextField::make('name')->readonly();

        $this->assertTrue($field->readonly);
    }

    /** @test */
    public function it_can_set_help_text(): void
    {
        $field = TextField::make('name')->helpText('Enter your full name');

        $this->assertEquals('Enter your full name', $field->getHelpText());
    }

    /** @test */
    public function it_generates_label_from_field_name_when_not_provided(): void
    {
        $field = TextField::make('first_name');

        $this->assertEquals('First name', $field->label);
    }
}
