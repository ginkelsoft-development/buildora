<?php

namespace Ginkelsoft\Buildora\Fields;

use Ginkelsoft\Buildora\Fields\Traits\HasSearch;
use Ginkelsoft\Buildora\Fields\Traits\HasValidation;
use Ginkelsoft\Buildora\Fields\Traits\HasVisibility;
use Ginkelsoft\Buildora\Fields\Traits\HasLayout;

/**
 * Class Field
 *
 * Represents a generic form or table field within the Buildora system.
 */
class Field
{
    use HasSearch;
    use HasVisibility;
    use HasLayout;
    use HasValidation;

    public string $name;
    public string $label;
    public string $type;
    public bool $sortable = false;
    public bool $readonly = false;
    public mixed $displayValue = null;
    public mixed $value = null;

    protected ?string $helpText = null;

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
     * Static factory method to create a new field instance.
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
     * Set the display label for the field.
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
     * Mark the field as sortable.
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
     * Mark the field as read-only.
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
     * Set the help text for the field.
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
     * Get the help text.
     *
     * @return string|null
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    /**
     * Get the value to display in a table or detail view.
     *
     * @param mixed $model
     * @return string
     */
    public function getDisplayValue(mixed $model): string
    {
        $value = $model->{$this->name} ?? null;
        return is_null($value) ? '-' : e($value);
    }

    /**
     * Hide the field from the table view.
     *
     * @return self
     */
    public function hideFromTable(): self
    {
        return $this->hide('table');
    }

    /**
     * Hide the field from the create form.
     *
     * @return self
     */
    public function hideFromCreate(): self
    {
        return $this->hide('create');
    }

    /**
     * Hide the field from the edit form.
     *
     * @return self
     */
    public function hideFromEdit(): self
    {
        return $this->hide('edit');
    }

    /**
     * Hide the field from export output.
     *
     * @return self
     */
    public function hideFromExport(): self
    {
        return $this->hide('export');
    }

    /**
     * Hide the field from the detail view.
     *
     * @return self
     */
    public function hideFromDetail(): self
    {
        return $this->hide('detail');
    }

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
            'helpText' => $this->helpText,
            'columnSpan' => $this->columnSpan,
            'startNewRow' => $this->startNewRow,
        ];
    }
}
