<?php

namespace Ginkelsoft\Buildora\Fields\Traits;

/**
 * Trait HasLayout
 *
 * Provides layout-related configuration for fields, such as column span and row positioning.
 */
trait HasLayout
{
    /**
     * Whether this field should start a new row in the form layout.
     *
     * @var bool
     */
    protected bool $startNewRow = false;

    /**
     * The column span configuration per responsive breakpoint.
     *
     * @var array<string, int>
     */
    protected array $columnSpan = ['default' => 12];

    /**
     * Set the column span for this field.
     * Accepts either a single integer or an array per breakpoint.
     *
     * @param int|array<string, int> $value
     * @return static
     */
    public function columnSpan(int|array $value): static
    {
        $this->columnSpan = is_array($value)
            ? $value
            : ['default' => $value];

        return $this;
    }

    /**
     * Get the column span configuration.
     *
     * @return array<string, int>
     */
    public function getColumnSpan(): array
    {
        return $this->columnSpan;
    }

    /**
     * Set whether this field should start a new row in the layout.
     *
     * @param bool $value
     * @return static
     */
    public function startNewRow(bool $value = true): static
    {
        $this->startNewRow = $value;
        return $this;
    }

    /**
     * Determine whether this field should start a new row.
     *
     * @return bool
     */
    public function shouldStartNewRow(): bool
    {
        return $this->startNewRow;
    }
}
