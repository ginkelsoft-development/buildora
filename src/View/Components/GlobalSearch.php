<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GlobalSearch extends Component
{
    /**
     * Render the component view.
     *
     * @return View|Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('buildora::components.global-search');
    }
}
