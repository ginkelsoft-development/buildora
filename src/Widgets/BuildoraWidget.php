<?php

namespace Ginkelsoft\Buildora\Widgets;

use Illuminate\View\View;

abstract class BuildoraWidget
{
    protected mixed $resource = null;
    protected mixed $model = null;
    protected array $colSpan = ['default' => 1];

    public static function make(): static
    {
        return new static();
    }

    public function setResource(mixed $resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    public function setModel(mixed $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getResource(): ?object
    {
        return $this->resource;
    }

    public function getModel(): ?object
    {
        return $this->model;
    }

    public function colSpan(int|array $value): static
    {
        $this->colSpan = is_array($value) ? $value : ['default' => $value];
        return $this;
    }

    public function getColSpan(): array
    {
        return $this->colSpan;
    }

    public function view(string $viewName): View
    {
        return view($viewName, [
            'resource' => $this->getResource(),
            'model' => $this->getModel(),
            'widget' => $this,
        ]);
    }

    abstract public function render(): View;

    public function pageVisibility(): array
    {
        return ['index'];
    }
}
