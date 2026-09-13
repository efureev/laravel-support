<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Exceptions;

use LogicException;

/**
 * Thrown when a parameter has a type or a value the callee cannot work with.
 *
 * Extends {@see LogicException}, so `catch (\LogicException)` keeps working.
 */
class InvalidParamException extends LogicException
{
    public function __construct(?string $message = null, public readonly ?string $name = null)
    {
        parent::__construct(
            $message ?? ($name === null ? 'Invalid parameter' : "Invalid parameter: $name")
        );
    }
}
