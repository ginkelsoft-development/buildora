<?php

namespace Ginkelsoft\Buildora\Layouts;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Support\ResourceResolver;

class Panel
{
    protected bool $inlineEditing = false;
    protected bool $inlineCreate = false;
    protected bool $inlineDelete = false;

    public function __construct(
        public string $relationName,
        public string $resourceClass,
        protected ?string $label = null,
    ) {
    }

    /**
     * Enable inline editing for this relation panel.
     * Allows editing related records in a modal without leaving the page.
     */
    public function inlineEditing(bool $create = true, bool $delete = true): static
    {
        $this->inlineEditing = true;
        $this->inlineCreate = $create;
        $this->inlineDelete = $delete;
        return $this;
    }

    /**
     * Check if inline editing is enabled.
     */
    public function hasInlineEditing(): bool
    {
        return $this->inlineEditing;
    }

    /**
     * Check if inline create is enabled.
     */
    public function canInlineCreate(): bool
    {
        return $this->inlineEditing && $this->inlineCreate;
    }

    /**
     * Check if inline delete is enabled.
     */
    public function canInlineDelete(): bool
    {
        return $this->inlineEditing && $this->inlineDelete;
    }

    /**
     * Koppel een relationele resource op basis van een relatienaam (zoals 'orders')
     */
    public static function resource(string $relationName): static
    {
        $resourceClass = BuildoraResource::resolveFromRelation($relationName);
        return new static($relationName, $resourceClass);
    }

    /**
     * Koppel een resource op basis van een method op het model
     */
    public static function fromMethod(string $relationMethod): static
    {
        // Let op: dit moet pas worden uitgevoerd als je het model kent (bijv. op runtime)
        // Dus hier bewaren we alleen de methodenaam – en vullen we resourceClass later pas in
        return new static($relationMethod, 'dynamic'); // tijdelijke placeholder
    }

    public function usingModel(object $model): static
    {
        // Alleen bepalen WELKE resourceclass hoort bij de relatie, geen data ophalen
        $relation = $model->{$this->relationName}();
        $relatedModel = $relation->getRelated();
        $base = class_basename($relatedModel);

        $resourceClass = "App\\Buildora\\Resources\\{$base}Buildora";

        if (!class_exists($resourceClass)) {
            throw new \Exception("Buildora resource [{$resourceClass}] not found.");
        }

        $this->resourceClass = $resourceClass;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public static function relation(string $method, string $resourceClass): static
    {
        return new static(
            relationName: $method,
            resourceClass: $resourceClass
        );
    }

    public function toArray(): array
    {
        return [
            'type'           => 'panel',
            'relation'       => $this->relationName,
            'resource'       => $this->resourceClass,
            'label'          => $this->label ?? ucfirst($this->relationName),
            'inlineEditing'  => $this->inlineEditing,
            'inlineCreate'   => $this->inlineCreate,
            'inlineDelete'   => $this->inlineDelete,
        ];
    }
}
