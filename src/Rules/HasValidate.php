<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Reusable one-off validation and request-reading helpers.
 *
 * @see https://laravel.com/docs/13.x/validation#manually-creating-validators
 */
trait HasValidate
{
    /**
     * Validate a single value, returning it unchanged when it passes.
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @see https://laravel.com/docs/13.x/validation#manually-creating-validators
     *
     * @example static::validateValue($id, 'required|uuid');
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
     * Validate a set of values, returning them unchanged when they pass.
     *
     * @param array<string, mixed> $values
     * @param array<string, mixed> $rules
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @see https://laravel.com/docs/13.x/validation#manually-creating-validators
     *
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

    /**
     * Read `$name` from the request as a bool.
     *
     * Strings are interpreted the HTML-form way: `"1"`, `"true"`, `"on"`, `"yes"` are true,
     * everything else — including unparseable strings — is false. Absent and explicitly
     * `null` values return `null` so the caller can tell "not sent" from "sent as false".
     *
     * @see https://www.php.net/manual/en/filter.filters.validate.php
     */
    protected static function gainBoolValue(
        Request $request,
        string $name,
        ?bool $default = null
    ): ?bool {
        $value = $request->get($name, $default);

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return (bool)$value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Read `$name` from the request as a float.
     *
     * Mirrors {@see self::gainIntValue()}, including its zero-is-absent behaviour.
     */
    protected static function gainFloatValue(
        Request $request,
        string $name,
        ?float $default = null
    ): ?float {
        $value = $request->get($name, $default);

        if ($value === null) {
            return null;
        }

        return ((float)$value) ?: $default;
    }

    /**
     * Read `$name` from the request as a list.
     *
     * A scalar is wrapped into a single-element array, so a field that may arrive either way
     * needs no branching at the call site. Absent and explicitly null values return `$default`.
     *
     * @param array<array-key, mixed>|null $default
     *
     * @return array<array-key, mixed>|null
     */
    protected static function gainArrayValue(
        Request $request,
        string $name,
        ?array $default = null
    ): ?array {
        $value = $request->get($name, $default);

        if ($value === null) {
            return $default;
        }

        return is_array($value) ? $value : [$value];
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
