<?php

namespace Ginkelsoft\Buildora\Fields;

/**
 * Class Field
 *
 * Represents a generic form or table field within the Buildora system.
 * Supports visibility toggles, searchability, read-only state, labels, help text, and export behavior.
 */
class Field
{
    protected bool $isSearchable = false;
    protected string $searchable;
    public string $name;
    public string $label;
    public string $type;
    protected ?string $helpText = null;
    public bool $sortable = false;
    public bool $readonly = false;

    /**
     * Visibility context array:
     * - table, create, edit, export, detail
     */
    public array $visibility = [
        'table' => true,
        'create' => true,
        'edit' => true,
        'export' => true,
    ];

    public mixed $value = null;

    /**
     * Field constructor.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     */
    public function __construct(string $name, ?string $label = null, string $type = 'text')
    {
        $this->name = $name;
        $this->label = $label ?? ucfirst(str_replace('_', ' ', $name));
        $this->type = $type;
    }

    /**
     * Static factory method to create a new field.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     * @return self
     */
    public static function make(string $name, ?string $label = null, string $type = 'text'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Set a custom label.
     *
     * @param string $label
     * @return self
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Enable or disable sorting for this field.
     *
     * @param bool $condition
     * @return self
     */
    public function sortable(bool $condition = true): self
    {
        $this->sortable = $condition;
        return $this;
    }

    /**
     * Set the field as readonly.
     *
     * @param bool $condition
     * @return self
     */
    public function readonly(bool $condition = true): self
    {
        $this->readonly = $condition;
        return $this;
    }

    /**
     * Set visibility for a specific context (table, create, edit, etc).
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
     * Hide the field in a given context.
     *
     * @param string $context
     * @return self
     */
    public function hide(string $context): self
    {
        return $this->show($context, false);
    }

    /**
     * Check if the field is visible in a given context.
     *
     * @param string $context
     * @return bool
     */
    public function isVisible(string $context): bool
    {
        return $this->visibility[$context] ?? false;
    }

    /**
     * Enable searching on this field.
     *
     * @param string $term
     * @return static
     */
    public function searchable(string $term = ''): static
    {
        if (! $this->supportsSearch()) {
            throw new \LogicException(static::class . " does not support searchable().");
        }

        $this->isSearchable = true;
        $this->searchable = $term ?: $this->name;

        return $this;
    }

    /**
     * Get the database column used for searching.
     *
     * @return string
     */
    public function getSearchColumn(): string
    {
        return $this->searchable ?? $this->name;
    }

    /**
     * Determine whether the field is marked as searchable.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->supportsSearch() && $this->isSearchable;
    }

    /**
     * Determine if this field type supports searching.
     *
     * @return bool
     */
    public function supportsSearch(): bool
    {
        return true;
    }

    /**
     * Set help text for the field.
     *
     * @param string $text
     * @return self
     */
    public function help(string $text): self
    {
        $this->helpText = $text;
        return $this;
    }

    /**
     * Get the help text associated with this field.
     *
     * @return string|null
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    // Shorthand methods for visibility toggles

    public function showInTable(): self { return $this->show('table'); }
    public function showInCreate(): self { return $this->show('create'); }
    public function showInEdit(): self { return $this->show('edit'); }
    public function showInDetail(): self { return $this->show('detail'); }
    public function showInExport(): self { return $this->show('export'); }

    public function hideFromTable(): self { return $this->hide('table'); }
    public function hideFromCreate(): self { return $this->hide('create'); }
    public function hideFromEdit(): self { return $this->hide('edit'); }
    public function hideFromDetail(): self { return $this->hide('detail'); }
    public function hideFromExport(): self { return $this->hide('export'); }

    /**
     * Convert the field into an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'sortable' => $this->sortable,
            'readonly' => $this->readonly,
            'visibility' => $this->visibility,
            'value' => $this->value,
        ];
    }
}
