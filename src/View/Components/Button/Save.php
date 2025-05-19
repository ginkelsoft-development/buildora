<?php

namespace Ginkelsoft\Buildora\View\Components\Button;

use Illuminate\View\Component;
use Illuminate\View\View;

class Save extends Component
{
    /**
     * The button label text.
     *
     * @var string
     */
    public string $label;

    /**
     * Create a new component instance.
     *
     * @param string $label
     */
    public function __construct(string $label = 'Save')
    {
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('buildora::components.button.save');
    }
}
