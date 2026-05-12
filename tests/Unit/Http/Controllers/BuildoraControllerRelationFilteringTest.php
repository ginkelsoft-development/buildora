<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Http\Controllers;

use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraController;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use stdClass;

/**
 * Regression coverage for the mass-assignment fix on the relations side.
 *
 * Before the fix, BuildoraController::store()/update() passed $request->all()
 * straight into handleRelationships(). A caller could inject any relation-method
 * name that existed on the model — `roles`, `permissions`, etc. — even if the
 * resource never declared those fields, and have them sync()'d.
 *
 * The fix narrows the payload to keys defined in defineFields().
 */
class BuildoraControllerRelationFilteringTest extends TestCase
{
    #[Test]
    public function relation_payload_only_contains_keys_declared_in_resolve_fields(): void
    {
        $controller = new BuildoraController();
        $method = new ReflectionMethod($controller, 'relationPayload');
        $method->setAccessible(true);

        $resource = new class {
            public function resolveFields(mixed $model): array
            {
                return [
                    TextField::make('name'),
                    TextField::make('email'),
                ];
            }
        };

        $model = new stdClass();

        $request = Request::create('/x', 'POST', [
            'name'     => 'Wietse',
            'email'    => 'wietse@example.com',
            'roles'    => [1, 2, 3], // hostile: not declared in defineFields()
            'is_admin' => true,      // hostile: not declared in defineFields()
        ]);

        $payload = $method->invoke($controller, $resource, $model, $request);

        $this->assertSame(['name', 'email'], array_keys($payload));
        $this->assertArrayNotHasKey('roles', $payload);
        $this->assertArrayNotHasKey('is_admin', $payload);
    }

    #[Test]
    public function relation_payload_is_empty_when_resource_declares_no_fields(): void
    {
        $controller = new BuildoraController();
        $method = new ReflectionMethod($controller, 'relationPayload');
        $method->setAccessible(true);

        $resource = new class {
            public function resolveFields(mixed $model): array
            {
                return [];
            }
        };

        $request = Request::create('/x', 'POST', [
            'attempted_relation' => [99],
        ]);

        $payload = $method->invoke($controller, $resource, new stdClass(), $request);

        $this->assertSame([], $payload);
    }

    #[Test]
    public function relation_payload_keeps_declared_relation_keys(): void
    {
        $controller = new BuildoraController();
        $method = new ReflectionMethod($controller, 'relationPayload');
        $method->setAccessible(true);

        // When a resource *does* declare a relation field, the key is allowed
        // through — handleRelationships() will then decide whether to sync it.
        $resource = new class {
            public function resolveFields(mixed $model): array
            {
                return [
                    TextField::make('name'),
                    TextField::make('roles'),
                ];
            }
        };

        $request = Request::create('/x', 'POST', [
            'name'  => 'Wietse',
            'roles' => [1, 2],
        ]);

        $payload = $method->invoke($controller, $resource, new stdClass(), $request);

        $this->assertSame(['name' => 'Wietse', 'roles' => [1, 2]], $payload);
    }
}
