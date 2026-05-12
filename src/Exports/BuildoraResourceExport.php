<?php

namespace Ginkelsoft\Buildora\Exports;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Streaming export of a Buildora resource.
 *
 * The previous implementation eager-loaded the entire result set with
 * `$query->get()` and held all rows in memory. For a moderately-sized
 * table (50k+ rows) that hits PHP's memory limit before the first byte
 * leaves the server.
 *
 * This class wires the export into laravel-excel's chunked-reading path:
 *   - FromQuery: hands the Eloquent builder over instead of a materialised
 *     result. The Excel sheet writer pulls rows itself.
 *   - WithChunkReading: pulls rows in batches (default 500) so peak
 *     memory stays bounded regardless of dataset size.
 *   - WithMapping: per-row transformation hook. Each model is hydrated
 *     into a per-row resource clone so display values, sanitisers, and
 *     formatters still apply.
 *
 * Headings come from the resource's *table-visible* fields; row values
 * are filtered to those marked as export-visible (allowing
 * ->hideFromExport() to drop sensitive columns).
 */
class BuildoraResourceExport implements FromQuery, WithHeadings, WithTitle, WithMapping, WithChunkReading
{
    private const DEFAULT_CHUNK_SIZE = 500;

    public function __construct(
        protected BuildoraResource $resource,
        protected Builder $query,
        protected string $title,
        protected int $chunkSize = self::DEFAULT_CHUNK_SIZE,
    ) {
    }

    /**
     * The Eloquent builder laravel-excel will iterate.
     *
     * @return Builder<Model>
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * Row count per memory-bounded batch.
     */
    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Per-row transformation. Each model is filled into a fresh resource
     * clone so type-specific setValue/getDisplayValue logic runs without
     * cross-row state leakage.
     *
     * @param Model $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $resource = clone $this->resource;
        $resource->fill($row);

        return collect($resource->getFields())
            ->filter(fn ($field) => $field->visibility['export'] ?? true)
            ->map(function ($field) {
                $value = $field->displayValue ?? $field->value;
                return $this->formatCellValue($value);
            })
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return collect($this->resource->getFields())
            ->filter(fn ($field) => $field->visibility['export'] ?? true)
            ->map(fn ($field) => $field->label)
            ->values()
            ->toArray();
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * Coerce a field's value to something a spreadsheet cell can hold.
     * Arrays render as comma-joined, objects as JSON, scalars pass through.
     */
    private function formatCellValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_object($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
