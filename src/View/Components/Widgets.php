<?php

namespace Ginkelsoft\Buildora\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Widgets extends Component
{

    /**
     * The Buildora resource instance.
     *
     * @var mixed
     */
    protected mixed $resource;

    /**
     * The visibility context (e.g., 'index', 'detail').
     *
     * @var string
     */
    public string $visibility;

    public mixed $model;

    public function __construct($resource, string $visibility = 'index', $model = null)
    {
        $this->resource = $resource;
        $this->visibility = $visibility;
        $this->model = $model;
    }

    /**
     * Get the view that represents the component.
     *
     * @return View|string
     */
    public function render(): View|string
    {
        return view('buildora::components.widgets', [
            'resource' => $this->resource,
            'visibility' => $this->visibility,
            'model' => $this->model,
        ]);
    }

    public function getResource(): mixed
    {
        return $this->resource;
    }

    public function setResource(mixed $resource): static
    {
        $this->resource = $resource;
        return $this;
    }
}
