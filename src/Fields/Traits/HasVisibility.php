<?php

namespace Ginkelsoft\Buildora\Fields\Traits;

/**
 * Trait HasVisibility
 *
 * Adds visibility handling for different field contexts like table, create, edit, etc.
 */
trait HasVisibility
{
    /**
     * Visibility configuration for different contexts.
     *
     * Supported contexts: table, create, edit, export, detail.
     *
     * @var array<string, bool>
     */
    public array $visibility = [
        'table' => true,
        'create' => true,
        'edit' => true,
        'export' => true,
        'detail' => true,
    ];

    /**
     * Set visibility for a given context.
     *
     * @param string $context
     * @param bool $condition
     * @return self
     */
    public function show(string $context, bool $condition = true): self
    {
        if (isset($this->visibility[$context])) {
            $this->visibility[$context] = $condition;
        }
        return $this;
    }

    /**
     * Hide the field in the given context.
     *
     * @param string $context
     * @return self
     */
    public function hide(string $context): self
    {
        return $this->show($context, false);
    }

    /**
     * Check if the field is visible in the given context.
     *
     * @param string $context
     * @return bool
     */
    public function isVisible(string $context): bool
    {
        return $this->visibility[$context] ?? false;
    }
}
