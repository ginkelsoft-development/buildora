<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Authorization;

use Ginkelsoft\Buildora\Authorization\BuildoraAbility;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BuildoraAbilityTest extends TestCase
{
    /**
     * @return array<string, array{0: BuildoraAbility, 1: string}>
     */
    public static function abilityValues(): array
    {
        return [
            'view'   => [BuildoraAbility::View,   'view'],
            'create' => [BuildoraAbility::Create, 'create'],
            'edit'   => [BuildoraAbility::Edit,   'edit'],
            'delete' => [BuildoraAbility::Delete, 'delete'],
        ];
    }

    /**
     * Values must stay exactly equal to the legacy string literals — if any
     * of these change, every existing row in the permissions table needs a
     * migration.
     */
    #[Test]
    #[DataProvider('abilityValues')]
    public function each_case_keeps_its_legacy_string_value(BuildoraAbility $ability, string $expected): void
    {
        $this->assertSame($expected, $ability->value);
    }

    #[Test]
    public function permission_string_composes_resource_and_value_with_a_dot(): void
    {
        $this->assertSame('user.view',     BuildoraAbility::View->permissionString('user'));
        $this->assertSame('post.create',   BuildoraAbility::Create->permissionString('post'));
        $this->assertSame('order.edit',    BuildoraAbility::Edit->permissionString('order'));
        $this->assertSame('invoice.delete', BuildoraAbility::Delete->permissionString('invoice'));
    }

    #[Test]
    public function defaults_returns_the_full_crud_quartet_in_a_stable_order(): void
    {
        // Order matters: GeneratePermissionsCommand prints them in this order
        // and tests/integrations may rely on it.
        $this->assertSame(
            [
                BuildoraAbility::View,
                BuildoraAbility::Create,
                BuildoraAbility::Edit,
                BuildoraAbility::Delete,
            ],
            BuildoraAbility::defaults()
        );
    }

    #[Test]
    public function cases_count_is_locked(): void
    {
        // Adding a new ability is a deliberate change — make sure new
        // abilities are also wired into defaults() and any consuming code.
        $this->assertCount(4, BuildoraAbility::cases());
    }
}
