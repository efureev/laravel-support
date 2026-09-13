<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Exceptions;

use RuntimeException;

/**
 * Thrown when a method exists but the current state forbids calling it.
 *
 * Extends {@see RuntimeException}, so `catch (\RuntimeException)` keeps working.
 */
class MethodNotAllowedException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message = 'Method Not Allowed')
    {
        parent::__construct($message === '' ? $reason : "$message: $reason");
    }
}
