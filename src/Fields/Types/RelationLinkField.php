<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Class RelationLinkField
 *
 * Displays a clickable link to a related record in the datatable.
 * Useful for showing relations as navigable links instead of plain text.
 */
class RelationLinkField extends Field
{
    protected ?string $relatedModel = null;
    protected ?string $relatedResource = null;
    protected string $displayColumn = 'name';
    protected ?string $linkRoute = null;
    protected ?Model $parentModel = null;
    protected bool $openInNewTab = false;

    /**
     * RelationLinkField constructor.
     */
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label ?? ucfirst($name), 'relationLink');

        // Default: hide from forms, show only in table/detail
        $this->hideFromCreate();
        $this->hideFromEdit();
    }

    /**
     * Factory method to create a new RelationLinkField instance.
     */
    public static function make(string $name, ?string $label = null, string $type = 'relationLink'): self
    {
        return new self($name, $label);
    }

    /**
     * Set the related model class.
     */
    public function relatedTo(string $model): self
    {
        $this->relatedModel = $model;
        return $this;
    }

    /**
     * Set the related Buildora resource class for generating the link.
     */
    public function resource(string $resourceClass): self
    {
        $this->relatedResource = $resourceClass;
        return $this;
    }

    /**
     * Set the column to display as link text.
     */
    public function displayUsing(string $column): self
    {
        $this->displayColumn = $column;
        return $this;
    }

    /**
     * Set a custom route for the link.
     */
    public function route(string $routeName): self
    {
        $this->linkRoute = $routeName;
        return $this;
    }

    /**
     * Open link in new tab.
     */
    public function openInNewTab(bool $value = true): self
    {
        $this->openInNewTab = $value;
        return $this;
    }

    /**
     * Set the parent model for relation inference.
     */
    public function setParentModel(Model $parentModel): self
    {
        $this->parentModel = $parentModel;
        return $this;
    }

    /**
     * Get the related model class name.
     */
    public function getRelatedModel(): ?string
    {
        if ($this->relatedModel) {
            return $this->relatedModel;
        }

        if ($this->parentModel && method_exists($this->parentModel, $this->name)) {
            $relation = $this->parentModel->{$this->name}();
            return get_class($relation->getRelated());
        }

        return null;
    }

    /**
     * Get the resource slug for URL generation.
     */
    protected function getResourceSlug(): ?string
    {
        if ($this->relatedResource) {
            // Extract slug from resource class name
            $className = class_basename($this->relatedResource);
            return Str::kebab(str_replace('Buildora', '', $className));
        }

        // Try to derive from related model
        $relatedModel = $this->getRelatedModel();
        if ($relatedModel) {
            return Str::kebab(class_basename($relatedModel));
        }

        return null;
    }

    /**
     * Set the field value from a model instance.
     */
    public function setValue(mixed $model): self
    {
        if (!$model instanceof Model) {
            $this->value = null;
            $this->displayValue = '-';
            return $this;
        }

        if (!method_exists($model, $this->name)) {
            $this->value = null;
            $this->displayValue = '-';
            return $this;
        }

        $relation = $model->{$this->name}();

        if (!($relation instanceof BelongsTo || $relation instanceof HasOne)) {
            $this->value = null;
            $this->displayValue = '-';
            return $this;
        }

        $relatedInstance = $relation->getResults();

        if (!$relatedInstance) {
            $this->value = null;
            $this->displayValue = '-';
            return $this;
        }

        $this->value = $relatedInstance->getKey();
        $displayText = $relatedInstance->{$this->displayColumn} ?? $relatedInstance->getKey();

        // Generate the link
        $resourceSlug = $this->getResourceSlug();
        $routeName = $this->linkRoute ?? 'buildora.show';

        if ($resourceSlug && \Route::has($routeName)) {
            $url = route($routeName, [
                'resource' => $resourceSlug,
                'id' => $relatedInstance->getKey(),
            ]);

            $target = $this->openInNewTab ? ' target="_blank"' : '';
            $this->displayValue = sprintf(
                '<a href="%s" class="text-indigo-600 hover:text-indigo-800 hover:underline"%s>%s</a>',
                $url,
                $target,
                e($displayText)
            );
        } else {
            $this->displayValue = e($displayText);
        }

        return $this;
    }

    /**
     * Get the display value for tables/views.
     */
    public function getDisplayValue(mixed $model): string
    {
        $this->setValue($model);
        return $this->displayValue ?? '-';
    }
}
