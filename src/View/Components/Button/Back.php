<?php

namespace Ginkelsoft\Buildora\View\Components\Button;

use Illuminate\View\Component;
use Illuminate\View\View;

class Back extends Component
{
    /**
     * Render the component view.
     *
     * @return View
     */
    public function render(): View
    {
        return view('buildora::components.button.back');
    }
}
