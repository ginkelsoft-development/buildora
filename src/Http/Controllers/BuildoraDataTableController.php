<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Ginkelsoft\Buildora\Datatable\BuildoraDatatable;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller;

class BuildoraDataTableController extends Controller
{
    /**
     * Display the index view with the datatable columns for the given resource.
     *
     * @param string $model The resource name (e.g., "user").
     * @return View
     */
    public function index(string $model): View
    {
        $datatable = new BuildoraDatatable($model);
        $columns = $datatable->getColumns();
        $resource = ResourceResolver::resolve($model);
        $pageActions = $resource->getPageActions();

        return view('buildora::index', compact('columns', 'model', 'resource', 'pageActions'));
    }

    /**
     * Return a JSON response with the data for the datatable.
     *
     * @param Request $request The current request instance.
     * @param string $resource The resource name (e.g., "user").
     * @return JsonResponse
     */
    public function json(Request $request, string $resource): JsonResponse
    {
        $search = (string) $request->input('search', '');
        $sortBy = (string) $request->input('sortBy', '');
        $sortDirection = (string) $request->input('sortDirection', 'asc');
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);

        $datatable = new BuildoraDatatable($resource);

        return response()->json(
            $datatable->getJsonResponse($search, $sortBy, $sortDirection, $perPage, $page)
        );
    }
}
