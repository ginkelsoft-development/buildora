<?php

namespace Ginkelsoft\Buildora\View\Components;

use Illuminate\View\Component;

class BuildoraIcon extends Component
{
    public string $icon;
    public string $fallback;
    public string $class;

    /**
     * Constructor voor de BuildoraIcon component.
     *
     * @param string $icon FontAwesome icoon naam (bijv. 'plus', 'trash', 'edit')
     * @param string $fallback Alternatieve tekst als FontAwesome niet is geladen
     * @param string $class Extra CSS-klassen voor styling
     */
    public function __construct(string $icon = 'circle-question', string $fallback = '❓', string $class = '')
    {
        $this->icon = $icon;
        $this->fallback = $fallback;
        $this->class = trim($icon . ' ' . $class);
    }

    /**
     * Render de component view.
     */
    public function render()
    {
        return view('buildora::components.icon');
    }
}
