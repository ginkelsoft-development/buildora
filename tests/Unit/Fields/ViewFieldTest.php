<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\ViewField;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ViewFieldTest extends TestCase
{
    #[Test]
    public function make_derives_a_label_from_the_field_name(): void
    {
        // Before #142, ViewField::make() defaulted $label to '' (empty
        // string, not null), which short-circuited the constructor's
        // `$label ?? ucfirst($name)` fallback and left the label empty.
        $field = ViewField::make('first_name');

        $this->assertNotNull($field->label);
        $this->assertNotSame('', $field->label);
        $this->assertSame('First_name', $field->label);
    }

    #[Test]
    public function make_accepts_an_explicit_label(): void
    {
        $field = ViewField::make('first_name', 'Voornaam');

        $this->assertSame('Voornaam', $field->label);
    }

    #[Test]
    public function make_uses_view_as_default_type(): void
    {
        $field = ViewField::make('first_name');

        $this->assertSame('view', $field->type);
    }
}
