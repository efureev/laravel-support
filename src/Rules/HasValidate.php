<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Illuminate\Support\Facades\Validator;

/**
 * Trait HasValidate
 * @package Php\Support\Laravel\Rules
 */
trait HasValidate
{
    /**
     * @param mixed $value
     * @param string $rules
     * @param string $attributeName
     * @param string|null $message
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     *
     * @example
     * static::validateValue($id, 'required|uuid');
     *
     */
    protected static function validateValue(
        mixed $value,
        string $rules,
        string $attributeName = 'value',
        string $message = null
    ): mixed {
        Validator::make(
            [$attributeName => $value],
            [$attributeName => $rules],
            $message ? [$attributeName => $message] : []
        )->validate();

        return $value;
    }
}
