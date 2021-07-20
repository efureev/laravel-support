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
     * @param string $value
     * @param string $rules
     * @param string $attributeName
     * @param string|null $message
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     *
     * @example
     * static::validateValue($id, 'required|uuid');
     *
     */
    protected static function validateValue(
        string $value,
        string $rules,
        string $attributeName = 'value',
        string $message = null
    ): array {
        return Validator::make(
            [$attributeName => $value],
            [$attributeName => $rules],
            $message ? [$attributeName => $message] : []
        )->validate();
    }
}
