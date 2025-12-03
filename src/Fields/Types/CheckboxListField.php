<?php

namespace Ginkelsoft\Buildora\Fields\Types;

/**
 * Checkbox list field for BelongsToMany relations.
 * Displays all options as checkboxes with select all/deselect all functionality.
 * Extends BelongsToManyField to reuse relation handling logic.
 */
class CheckboxListField extends BelongsToManyField
{
    protected bool $groupByPrefix = false;
    protected string $groupSeparator = '.';

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label, 'checkboxList');
    }

    public static function make(string $name, ?string $label = null, string $type = 'checkboxList'): self
    {
        return new self($name, $label);
    }

    /**
     * Enable grouping by prefix (useful for permissions like "user.view", "user.edit").
     */
    public function groupByPrefix(bool $enabled = true, string $separator = '.'): self
    {
        $this->groupByPrefix = $enabled;
        $this->groupSeparator = $separator;
        return $this;
    }

    public function isGrouped(): bool
    {
        return $this->groupByPrefix;
    }

    public function getGroupSeparator(): string
    {
        return $this->groupSeparator;
    }

    /**
     * Get options grouped by prefix.
     */
    public function getGroupedOptions(): array
    {
        $options = $this->getOptions();
        $grouped = [];

        foreach ($options as $key => $label) {
            if ($this->groupByPrefix && str_contains($label, $this->groupSeparator)) {
                $parts = explode($this->groupSeparator, $label, 2);
                $group = ucfirst($parts[0]);
                $grouped[$group][$key] = $label;
            } else {
                $grouped[''][$key] = $label;
            }
        }

        ksort($grouped);
        return $grouped;
    }

    /**
     * Override setValue to return array of IDs instead of key-value pairs.
     */
    public function setValue(mixed $model): self
    {
        parent::setValue($model);

        // Convert to simple array of IDs if it's an associative array
        if (is_array($this->value) && !empty($this->value)) {
            $this->value = array_map('strval', array_keys($this->value));
        }

        return $this;
    }
}
