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
    public $resource;

    /**
     * The visibility context (e.g., 'index', 'detail').
     *
     * @var string
     */
    public string $visibility;

    /**
     * Create a new component instance.
     *
     * @param mixed $resource
     * @param string $visibility
     */
    public function __construct($resource, string $visibility = 'index')
    {
        $this->resource = $resource;
        $this->visibility = $visibility;
    }

    /**
     * Get the view that represents the component.
     *
     * @return View|string
     */
    public function render(): View|string
    {
        return view('buildora::components.widgets');
    }
}
