<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Exceptions;

use BadMethodCallException;

/**
 * Thrown when a required method is missing on the calling class.
 *
 * Extends {@see BadMethodCallException}, so `catch (\BadMethodCallException)` keeps working.
 */
class UnknownMethodException extends BadMethodCallException
{
    public function __construct(public readonly string $method, ?string $message = null)
    {
        parent::__construct($message ?? "Unknown method: $method");
    }
}
