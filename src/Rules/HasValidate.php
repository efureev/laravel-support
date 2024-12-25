<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Illuminate\Http\Request;
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
        ?string $message = null
    ): mixed {
        Validator::make(
            [$attributeName => $value],
            [$attributeName => $rules],
            $message ? [$attributeName => $message] : []
        )->validate();

        return $value;
    }

    /**
     * @param array $values
     * @param array $rules
     * @param array $messages
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @example static::validateValues($data, ['id' => 'required|uuid']);
     */
    protected static function validateValues(
        array $values,
        array $rules,
        array $messages = []
    ): array {
        Validator::make($values, $rules, $messages)->validate();

        return $values;
    }


    protected static function gainIntValue(
        Request $request,
        string $name,
        ?int $default = null
    ): ?int {
        $value = $request->get($name, $default);
        return $value === null ? null : (((int)$value) ?: $default);
    }

    protected static function gainBoolValue(
        Request $request,
        string $name,
        ?bool $default = null
    ): ?bool {
        $value = $request->get($name, $default);
        return $value === null ? null : isTrue($value);
    }

    protected static function gainStringValue(
        Request $request,
        string $name,
        ?string $default = null
    ): ?string {
        $value = (string)$request->get($name, $default);
        return $value ?: null;
    }
}
