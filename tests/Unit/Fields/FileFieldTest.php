<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\FileField;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Validation\Rules\BlocksExecutableUploads;
use PHPUnit\Framework\Attributes\Test;

class FileFieldTest extends TestCase
{
    #[Test]
    public function validation_rules_always_include_the_executable_blocker(): void
    {
        $field = FileField::make('avatar');

        $rules = $field->getValidationRules();

        $this->assertContains('file', $rules);
        $this->assertTrue(
            $this->hasRuleOfType($rules, BlocksExecutableUploads::class),
            'Expected BlocksExecutableUploads rule to be present by default.'
        );
    }

    #[Test]
    public function validation_rules_include_mimetypes_when_whitelist_is_set(): void
    {
        $field = FileField::make('avatar')->allowedMimeTypes(['image/jpeg', 'image/png']);

        $rules = $field->getValidationRules();

        $this->assertContains('mimetypes:image/jpeg,image/png', $rules);
    }

    #[Test]
    public function validation_rules_omit_mimetypes_when_no_whitelist_is_set(): void
    {
        $field = FileField::make('avatar');

        $rules = $field->getValidationRules();

        foreach ($rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'mimetypes:')) {
                $this->fail('Did not expect a mimetypes rule when no whitelist is configured.');
            }
        }

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validation_rules_include_max_size_when_set(): void
    {
        $field = FileField::make('avatar')->maxSize(2048);

        $rules = $field->getValidationRules();

        $this->assertContains('max:2048', $rules);
    }

    #[Test]
    public function allowed_mime_types_getter_returns_unique_filtered_values(): void
    {
        $field = FileField::make('avatar')->allowedMimeTypes(['image/png', '', 'image/png', 'image/jpeg']);

        $this->assertSame(['image/png', 'image/jpeg'], $field->getAllowedMimeTypes());
    }

    /**
     * @param array<int, mixed> $rules
     * @param class-string $type
     */
    private function hasRuleOfType(array $rules, string $type): bool
    {
        foreach ($rules as $rule) {
            if ($rule instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
