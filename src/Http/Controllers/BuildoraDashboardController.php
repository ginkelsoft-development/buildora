<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Ginkelsoft\Buildora\Support\ResourceScanner;

class BuildoraDashboardController extends Controller
{
    /**
     * Display the dashboard page with an overview of all Buildora resources.
     *
     * @return View
     */
    public function index(): View
    {
        return view('buildora::dashboard', [
            'resources' => ResourceScanner::getResources()
        ]);
    }
}
