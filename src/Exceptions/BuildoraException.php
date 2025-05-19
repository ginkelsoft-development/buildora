<?php

namespace Ginkelsoft\Buildora\Exceptions;

use Exception;

/**
 * Class BuildoraException
 *
 * Custom exception class for Buildora-related errors.
 * Prepends all messages with "[Buildora]" for easier debugging.
 */
class BuildoraException extends Exception
{
    /**
     * BuildoraException constructor.
     *
     * Creates a new BuildoraException instance with a prefixed message.
     *
     * @param string $message The exception message.
     * @param int $code The HTTP or internal error code (default: 500).
     */
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct('[Buildora] ' . $message, $code);
    }

    /**
     * Static helper to throw a BuildoraException directly.
     *
     * @param string $message The exception message.
     * @param int $code The error code (default: 500).
     * @return never
     *
     * @throws self
     */
    public static function throw(string $message, int $code = 500): never
    {
        throw new self($message, $code);
    }
}
