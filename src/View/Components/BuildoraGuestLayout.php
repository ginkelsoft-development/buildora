<?php

namespace Ginkelsoft\Buildora\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class BuildoraGuestLayout extends Component
{
    /**
     * The title to be shown in the layout.
     *
     * @var string
     */
    public string $title;

    /**
     * Create a new component instance.
     *
     * @param string $title
     */
    public function __construct(string $title = 'Buildora Management')
    {
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('buildora::layouts.buildora-guest');
    }
}
