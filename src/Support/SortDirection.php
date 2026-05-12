<?php

namespace Ginkelsoft\Buildora\Support;

/**
 * Normalises a sort-direction string coming from untrusted input
 * (e.g. a query parameter) into a strict "asc"|"desc" value.
 *
 * Laravel's QueryBuilder::orderBy() already throws on invalid directions,
 * but that surfaces as a 500 error to the user. Normalising at the edge
 * keeps datatable requests robust against typos and crafted payloads
 * without leaking exception details.
 */
final class SortDirection
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    /**
     * Return a safe direction string. Falls back to "asc" for anything
     * that is not strictly "asc" or "desc" (case-insensitive).
     */
    public static function normalize(mixed $direction): string
    {
        if (! is_string($direction)) {
            return self::ASC;
        }

        $lower = strtolower(trim($direction));

        return $lower === self::DESC ? self::DESC : self::ASC;
    }
}
