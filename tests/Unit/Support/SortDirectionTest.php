<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Support;

use Ginkelsoft\Buildora\Support\SortDirection;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class SortDirectionTest extends TestCase
{
    #[Test]
    public function asc_is_normalised_to_asc(): void
    {
        $this->assertSame('asc', SortDirection::normalize('asc'));
    }

    #[Test]
    public function desc_is_normalised_to_desc(): void
    {
        $this->assertSame('desc', SortDirection::normalize('desc'));
    }

    #[Test]
    public function casing_is_ignored(): void
    {
        $this->assertSame('asc', SortDirection::normalize('ASC'));
        $this->assertSame('desc', SortDirection::normalize('DESC'));
        $this->assertSame('desc', SortDirection::normalize('Desc'));
    }

    #[Test]
    public function surrounding_whitespace_is_trimmed(): void
    {
        $this->assertSame('asc', SortDirection::normalize('  asc  '));
        $this->assertSame('desc', SortDirection::normalize("\tdesc\n"));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function untrustedInputs(): array
    {
        return [
            'empty string'         => [''],
            'unknown word'         => ['foo'],
            'sql injection'        => ['asc; DROP TABLE users;--'],
            'numeric'              => ['123'],
            'null'                 => [null],
            'integer'              => [1],
            'boolean true'         => [true],
            'boolean false'        => [false],
            'array'                => [['asc']],
            'object'               => [new \stdClass()],
        ];
    }

    #[Test]
    #[DataProvider('untrustedInputs')]
    public function untrusted_or_invalid_input_falls_back_to_asc(mixed $input): void
    {
        $this->assertSame('asc', SortDirection::normalize($input));
    }
}
