<?php

namespace Ginkelsoft\Buildora\Http\Requests;

use Illuminate\Http\Request;

/**
 * Value object that captures the four input parameters every Buildora
 * datatable AJAX request carries.
 *
 * The controllers previously parsed these inline:
 *
 *   \$search = (string) \$request->input('search', '');
 *   \$sortBy = (string) \$request->input('sortBy', '');
 *   \$sortDirection = (string) \$request->input('sortDirection', 'asc');
 *   \$perPage = (int) \$request->input('per_page', 10);
 *   \$page = (int) \$request->input('page', 1);
 *
 * That worked, but skipped two defensive checks: sortDirection wasn't
 * normalised (a crafted 'asc; DROP …' fell through to Eloquent's
 * orderBy() which throws on invalid values — see #119), and perPage/page
 * accepted zero / negative inputs that pagination then mishandled.
 *
 * Centralising the parsing means future controllers get the same
 * sanitised inputs for free, and there is a single source of truth for
 * what "a datatable request" means.
 */
final class DatatableRequest
{
    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;

    public function __construct(
        public readonly string $search,
        public readonly string $sortBy,
        public readonly string $sortDirection,
        public readonly int $perPage,
        public readonly int $page,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            search:        (string) $request->input('search', ''),
            sortBy:        (string) $request->input('sortBy', ''),
            sortDirection: self::normalizeSortDirection($request->input('sortDirection', 'asc')),
            perPage:       self::clampPerPage((int) $request->input('per_page', self::DEFAULT_PER_PAGE)),
            page:          max(1, (int) $request->input('page', 1)),
        );
    }

    /**
     * Strict "asc"|"desc". Anything else (crafted SQL fragment, typo, wrong
     * type) falls back to "asc".
     *
     * NOTE: Buildora has a dedicated SortDirection helper that performs the
     * same normalisation; this duplicate guard exists so DatatableRequest
     * stays self-contained and doesn't add a cross-PR dependency. Consolidate
     * once the helper lands on the same baseline.
     */
    private static function normalizeSortDirection(mixed $value): string
    {
        if (! is_string($value)) {
            return 'asc';
        }

        $lower = strtolower(trim($value));

        return $lower === 'desc' ? 'desc' : 'asc';
    }

    private static function clampPerPage(int $value): int
    {
        if ($value < 1) {
            return self::DEFAULT_PER_PAGE;
        }

        if ($value > self::MAX_PER_PAGE) {
            return self::MAX_PER_PAGE;
        }

        return $value;
    }
}
