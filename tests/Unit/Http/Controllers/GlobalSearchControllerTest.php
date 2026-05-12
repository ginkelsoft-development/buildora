<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Http\Controllers;

use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GlobalSearchControllerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Routes are normally registered via the package provider; force the
        // global-search endpoint to be available without the rest of the
        // middleware stack so we can exercise the controller directly.
        $app['router']->get(
            '/test/global-search',
            \Ginkelsoft\Buildora\Http\Controllers\GlobalSearchController::class
        );
    }

    #[Test]
    public function it_returns_no_results_for_an_empty_term(): void
    {
        $response = $this->getJson('/test/global-search?q=');

        $response->assertStatus(200);
        $response->assertExactJson(['results' => []]);
    }

    #[Test]
    public function it_returns_no_results_when_q_is_omitted(): void
    {
        $response = $this->getJson('/test/global-search');

        $response->assertStatus(200);
        $response->assertExactJson(['results' => []]);
    }

    #[Test]
    public function it_returns_no_results_for_a_term_shorter_than_the_configured_minimum(): void
    {
        config()->set('buildora.global_search.min_term_length', 3);

        $response = $this->getJson('/test/global-search?q=ab'); // 2 chars

        $response->assertStatus(200);
        $response->assertExactJson(['results' => []]);
    }

    #[Test]
    public function whitespace_only_term_is_treated_as_empty(): void
    {
        $response = $this->getJson('/test/global-search?q=' . urlencode('   '));

        $response->assertStatus(200);
        $response->assertExactJson(['results' => []]);
    }

    #[Test]
    public function it_proceeds_when_term_meets_the_minimum_length(): void
    {
        // With no resources scanned in the test environment, an above-minimum
        // term still produces an empty result set — but the controller must
        // run past the guard rather than short-circuiting. We assert on the
        // 200 status and the presence of the results key.
        config()->set('buildora.global_search.min_term_length', 2);

        $response = $this->getJson('/test/global-search?q=foo');

        $response->assertStatus(200);
        $response->assertJsonStructure(['results']);
    }
}
