<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Middleware;

use Ginkelsoft\Buildora\Http\Middleware\CheckBuildoraPermission;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckBuildoraPermissionTest extends TestCase
{
    #[Test]
    public function it_aborts_with_403_when_no_resource_is_in_the_route(): void
    {
        $middleware = new CheckBuildoraPermission();

        $request = Request::create('/buildora/missing');
        $route = new Route(['GET'], '/buildora/{nothing?}', []);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        try {
            $middleware->handle($request, fn () => null, 'view');
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertStringContainsString('No resource specified', $e->getMessage());
        }
    }

    #[Test]
    public function it_aborts_with_403_when_user_lacks_the_required_permission(): void
    {
        $middleware = new CheckBuildoraPermission();

        $request = Request::create('/buildora/users');
        $route = new Route(['GET'], '/buildora/{resource}', []);
        $route->parameters = ['resource' => 'users'];
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        try {
            $middleware->handle($request, fn () => null, 'view');
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertStringContainsString('Unauthorized for permission: users.view', $e->getMessage());
        }
    }
}
