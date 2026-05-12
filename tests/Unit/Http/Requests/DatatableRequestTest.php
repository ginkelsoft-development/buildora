<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Http\Requests;

use Ginkelsoft\Buildora\Http\Requests\DatatableRequest;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;

class DatatableRequestTest extends TestCase
{
    private function requestWith(array $params): Request
    {
        return Request::create('/datatable', 'GET', $params);
    }

    #[Test]
    public function defaults_are_applied_when_no_parameters_are_present(): void
    {
        $params = DatatableRequest::fromRequest($this->requestWith([]));

        $this->assertSame('',     $params->search);
        $this->assertSame('',     $params->sortBy);
        $this->assertSame('asc',  $params->sortDirection);
        $this->assertSame(10,     $params->perPage);
        $this->assertSame(1,      $params->page);
    }

    #[Test]
    public function legitimate_values_are_passed_through(): void
    {
        $params = DatatableRequest::fromRequest($this->requestWith([
            'search'        => 'wietse',
            'sortBy'        => 'name',
            'sortDirection' => 'desc',
            'per_page'      => 25,
            'page'          => 3,
        ]));

        $this->assertSame('wietse', $params->search);
        $this->assertSame('name',   $params->sortBy);
        $this->assertSame('desc',   $params->sortDirection);
        $this->assertSame(25,       $params->perPage);
        $this->assertSame(3,        $params->page);
    }

    #[Test]
    public function sort_direction_is_normalised_via_SortDirection_helper(): void
    {
        // Defence-in-depth: a crafted value should not propagate.
        $params = DatatableRequest::fromRequest($this->requestWith([
            'sortDirection' => 'asc; DROP TABLE users;--',
        ]));

        $this->assertSame('asc', $params->sortDirection);
    }

    #[Test]
    public function sort_direction_casing_and_whitespace_are_handled(): void
    {
        $params = DatatableRequest::fromRequest($this->requestWith([
            'sortDirection' => '  DESC  ',
        ]));

        $this->assertSame('desc', $params->sortDirection);
    }

    #[Test]
    public function per_page_below_one_falls_back_to_default(): void
    {
        $a = DatatableRequest::fromRequest($this->requestWith(['per_page' => 0]));
        $b = DatatableRequest::fromRequest($this->requestWith(['per_page' => -10]));

        $this->assertSame(10, $a->perPage);
        $this->assertSame(10, $b->perPage);
    }

    #[Test]
    public function per_page_is_clamped_to_a_maximum(): void
    {
        $params = DatatableRequest::fromRequest($this->requestWith(['per_page' => 9999]));

        $this->assertSame(250, $params->perPage);
    }

    #[Test]
    public function page_is_clamped_to_at_least_one(): void
    {
        $a = DatatableRequest::fromRequest($this->requestWith(['page' => 0]));
        $b = DatatableRequest::fromRequest($this->requestWith(['page' => -5]));

        $this->assertSame(1, $a->page);
        $this->assertSame(1, $b->page);
    }

    #[Test]
    public function value_object_is_immutable(): void
    {
        $params = DatatableRequest::fromRequest($this->requestWith([]));

        // Trying to mutate a readonly property must throw.
        $this->expectException(\Error::class);
        $params->search = 'mutated';
    }
}
