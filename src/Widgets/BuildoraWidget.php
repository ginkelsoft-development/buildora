<?php

namespace Ginkelsoft\Buildora\Widgets;

use Illuminate\View\View;

abstract class BuildoraWidget
{
    /**
     * De zichtbaarheid van de widget per pagina.
     *
     * @return array
     */
    public function pageVisibility(): array
    {
        return ['index', 'create', 'edit', 'detail'];
    }

    /**
     * Render de widget.
     *
     * @return View
     */
    abstract public function render(): View;
}
