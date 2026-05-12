<?php

namespace Ginkelsoft\Buildora\Exports;

use Ginkelsoft\Buildora\Actions\BulkAction;
use Ginkelsoft\Buildora\Support\ResourceResolver;

/**
 * Class ExportManager
 *
 * Responsible for generating Excel or CSV exports for Buildora resources.
 */
class ExportManager
{
    /**
     * Generate an export instance for a given resource.
     *
     * Previously materialised the full result set via $query->get() and
     * built an in-memory array, which hit PHP memory limits on moderately
     * sized tables. The export now streams rows through laravel-excel's
     * chunked-reading pipeline (see BuildoraResourceExport).
     *
     * @param string $modelSlug The resource slug (e.g. 'users', 'posts').
     * @param array  $ids       The selected row IDs to export (optional).
     * @param string $format    The export format ('xlsx' or 'csv') — kept
     *                          for API compatibility; the file format is
     *                          decided by the controller's Excel::download
     *                          call, not here.
     */
    public function make(string $modelSlug, array $ids, string $format): BuildoraResourceExport
    {
        $resource = ResourceResolver::resolve($modelSlug);

        $builder = $resource::query()->getEloquentBuilder();

        if (! empty($ids)) {
            $builder->whereIn('id', $ids);
        }

        return new BuildoraResourceExport(
            resource: $resource,
            query:    $builder,
            title:    ucfirst($modelSlug),
        );
    }

    /**
     * Returns the default bulk export actions for a given resource.
     *
     * @param string $resourceSlug
     * @return array<int, BulkAction>
     */
    public static function defaultBulkActions(string $resourceSlug): array
    {
        return [
            BulkAction::make('Export to Excel', 'buildora.export', [
                'resource' => $resourceSlug,
                'format' => 'xlsx',
            ])->method('GET'),

            BulkAction::make('Export to CSV', 'buildora.export', [
                'resource' => $resourceSlug,
                'format' => 'csv',
            ])->method('GET'),
        ];
    }
}
