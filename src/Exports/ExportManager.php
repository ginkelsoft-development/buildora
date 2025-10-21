<?php

namespace Ginkelsoft\Buildora\Exports;

use Ginkelsoft\Buildora\Actions\BulkAction;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Class ExportManager
 *
 * Responsible for generating Excel or CSV exports for Buildora resources.
 */
class ExportManager
{
    /**
     * Generate an export file instance for a given model/resource.
     *
     * @param string $modelSlug The resource slug (e.g. 'users', 'posts').
     * @param array $ids The selected row IDs to export.
     * @param string $format The export format ('xlsx' or 'csv').
     * @return FromArray|WithHeadings|WithTitle
     */
    public function make(string $modelSlug, array $ids, string $format): FromArray|WithHeadings|WithTitle
    {
        $resource = ResourceResolver::resolve($modelSlug);
        $query = $resource::query();

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $data = $query->get();

        $fields = collect($resource->getFields())
            ->filter(fn($field) => $field->visibility['table'] ?? true);

        $headers = $fields->map(fn($f) => $f->label)->toArray();

        $rows = collect($data)->map(function ($resource) {
            return collect($resource->getFields())
                ->filter(fn($field) => $field->visibility['export'] ?? true)
                ->map(function ($field) {
                    $value = $field->value;

                    return is_array($value)
                        ? implode(', ', $value)
                        : (is_object($value) ? json_encode($value) : $value);
                })->toArray();
        });

        return new class (
            $headers,
            $rows->toArray(),
            ucfirst($modelSlug)
        ) implements FromArray, WithHeadings, WithTitle {
            public function __construct(
                protected array $headings,
                protected array $rows,
                protected string $title
            ) {
            }

            /**
             * Return the rows as an array for export.
             *
             * @return array
             */
            public function array(): array
            {
                return $this->rows;
            }

            /**
             * Return the column headers for the export.
             *
             * @return array
             */
            public function headings(): array
            {
                return $this->headings;
            }

            /**
             * Return the sheet title.
             *
             * @return string
             */
            public function title(): string
            {
                return $this->title;
            }
        };
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
