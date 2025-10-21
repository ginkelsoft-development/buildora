<?php

namespace Ginkelsoft\Buildora\Fields\Traits;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;

/**
 * Trait HasSearch
 *
 * Adds search-related capabilities to a field, such as enabling searching and defining search columns.
 */
trait HasSearch
{
    /**
     * Indicates whether the field is searchable.
     *
     * @var bool
     */
    protected bool $isSearchable = false;

    /**
     * The column to use for search queries.
     *
     * @var string|null
     */
    protected ?string $searchableColumn = null;

    /**
     * Enable search for this field.
     *
     * @param string|null $term The column name to search on, or null to default to the field name.
     * @return static
     * @throws \LogicException If the field does not support searching.
     */
    public function searchable(string $term = null): static
    {
        if (! $this->supportsSearch()) {
            throw new BuildoraException(static::class . " does not support searchable().");
        }

        $this->isSearchable = true;
        $this->searchableColumn = $term ?? $this->name;

        return $this;
    }

    /**
     * Determine if the field is currently searchable.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->supportsSearch() && $this->isSearchable;
    }

    /**
     * Get the column name used for search operations.
     *
     * @return string
     */
    public function getSearchColumn(): string
    {
        return $this->searchableColumn ?? $this->name;
    }

    /**
     * Indicate whether the field type supports searching.
     *
     * @return bool
     */
    public function supportsSearch(): bool
    {
        return true;
    }
}
