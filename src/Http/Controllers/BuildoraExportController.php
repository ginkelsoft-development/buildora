<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Ginkelsoft\Buildora\Exports\ExportManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BuildoraExportController extends Controller
{
    /**
     * Export data using a Buildora resource.
     *
     * @param Request $request The current HTTP request instance.
     * @param string $model The resource/model name (e.g., "user").
     * @param string $format The export format ("xlsx" or "csv").
     * @return BinaryFileResponse The downloadable export file.
     */
    public function export(Request $request, string $model, string $format = 'xlsx'): BinaryFileResponse
    {
        $ids = $this->getIdsFromRequest($request);

        if (!in_array($format, ['xlsx', 'csv'])) {
            abort(400, "Invalid format: {$format}. Supported formats: xlsx, csv");
        }

        $export = app(ExportManager::class)->make($model, $ids, $format);

        return Excel::download($export, "{$model}_export.{$format}");
    }

    /**
     * Extract and validate comma-separated IDs from the request.
     *
     * @param Request $request The current HTTP request instance.
     * @return array The filtered array of numeric IDs.
     */
    private function getIdsFromRequest(Request $request): array
    {
        return array_filter(
            explode(',', $request->query('ids', '')),
            fn ($id) => is_numeric($id) && (int) $id > 0
        );
    }
}
