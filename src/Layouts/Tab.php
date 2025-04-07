<?php

namespace Ginkelsoft\Buildora\Layouts;

use Ginkelsoft\Buildora\Resources\BuildoraResource;

class Tab
{
    public function __construct(
        public string $relationName,
        public string $resourceClass,
        protected ?string $label = null,
    ) {}

    public static function resource(string $relationName): static
    {
        $resourceClass = BuildoraResource::resolveFromRelation($relationName);

        return new static($relationName, $resourceClass);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'type'     => 'tab',
            'relation' => $this->relationName,
            'resource' => $this->resourceClass,
            'label'    => $this->label ?? ucfirst($this->relationName),
        ];
    }
}
