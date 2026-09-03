<?php

namespace Ginkelsoft\Buildora\Tests\Feature;

use Ginkelsoft\Buildora\Tests\TestCase;

/**
 * Regressietest voor issue #124: datatable- en bulk-action-routes moeten
 * binnen de 'web'-middlewaregroep vallen, zodat CSRF-verificatie actief is.
 *
 * Laravel schakelt CSRF-verificatie automatisch uit tijdens unit tests
 * (VerifyCsrfToken::runningUnitTests()), dus we forceren hier bewust een
 * niet-testing omgeving om de echte middleware-pijplijn te doorlopen zoals
 * die ook in productie draait.
 *
 * Een POST/PUT/DELETE zonder geldig CSRF-token op een Buildora-route moet
 * resulteren in HTTP 419 (TokenMismatchException). Krijgt de aanvraag
 * i.p.v. daarvan een 200/302 (bijv. een redirect naar de loginpagina),
 * dan betekent dat dat de actie zonder CSRF-token is doorgelaten tot in
 * de applicatielogica: een CSRF-kwetsbaarheid.
 */
class CsrfProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Forceer echte CSRF-afdwinging (zie class-docblock hierboven).
        $this->app['env'] = 'local';
    }

    public function test_post_to_switch_locale_without_csrf_token_is_rejected_with_419(): void
    {
        $response = $this->post('/buildora/switch-locale', [
            'locale' => 'nl',
        ]);

        $response->assertStatus(419);
    }

    public function test_post_to_resource_store_without_csrf_token_is_rejected_with_419(): void
    {
        // De dynamische resource-routes (store/update/destroy), waar ook
        // bulk-actions naartoe posten, zitten genest in dezelfde groep en
        // moeten dezelfde CSRF-bescherming erven.
        $response = $this->post('/buildora/resource/users', [
            'name' => 'Attacker',
        ]);

        $response->assertStatus(419);
    }

    public function test_delete_to_resource_destroy_without_csrf_token_is_rejected_with_419(): void
    {
        // DELETE wordt door bulk-delete-acties gebruikt.
        $response = $this->delete('/buildora/resource/users/1');

        $response->assertStatus(419);
    }

    public function test_switch_locale_route_carries_the_web_middleware_group(): void
    {
        // Directe assertie op de route-registratie: bewijst dat de fix
        // (of de bestaande, correcte registratie) niet per ongeluk wordt
        // teruggedraaid, onafhankelijk van omgevingsdetectie-trucs hierboven.
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('buildora.locale.switch');

        $this->assertContains('web', $route->gatherMiddleware());
    }

    public function test_resource_store_route_carries_the_web_middleware_group(): void
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('buildora.store');

        $this->assertContains('web', $route->gatherMiddleware());
    }
}
