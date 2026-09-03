<?php

namespace Ginkelsoft\Buildora\Tests\Feature;

use Ginkelsoft\Buildora\Tests\TestCase;

/**
 * Regressietest voor issue #124 (hardening): CSRF-bescherming mag niet
 * afhankelijk zijn van een correct ingevulde config('buildora.middleware').
 *
 * Simuleert een consumerende applicatie die het gepubliceerde
 * config/buildora.php aanpast en daarbij per ongeluk 'web' uit de
 * middleware-lijst verwijdert. Ook dan moet CSRF-verificatie actief
 * blijven, omdat de 'web'-groep hard-coded om de routes heen staat
 * (zie routes/buildora.php). Zonder deze hardcodering zou dit scenario
 * de datatable- en bulk-action-routes buiten CSRF-verificatie laten
 * vallen.
 */
class CsrfProtectionWithoutWebInConfigTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('buildora.middleware', [
            'buildora.auth',
            'buildora.ensure-user-resource',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Forceer echte CSRF-afdwinging; Laravel schakelt dit anders
        // automatisch uit tijdens unit tests.
        $this->app['env'] = 'local';
    }

    public function test_post_without_csrf_token_is_still_rejected_with_419_even_without_web_in_config(): void
    {
        $response = $this->post('/buildora/switch-locale', [
            'locale' => 'nl',
        ]);

        $response->assertStatus(419);
    }
}
