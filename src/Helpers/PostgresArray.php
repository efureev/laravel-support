<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Helpers;

use function array_values;
use function is_array;
use function json_encode;
use function str_replace;
use function strlen;

/**
 * Conversion between PHP arrays and the native PostgreSQL array literal format (`{a,b,c}`).
 *
 * PostgreSQL array columns (`text[]`, `integer[]`, …) are not JSON: the wire format is a
 * brace-delimited literal. Laravel has no helper for it, hence this one.
 *
 * @see https://www.postgresql.org/docs/current/arrays.html#ARRAYS-INPUT
 */
final class PostgresArray
{
    /**
     * Encode a PHP array as a PostgreSQL array literal.
     *
     * Nested arrays become nested literals. Keys are dropped — PostgreSQL arrays are ordered
     * lists, not maps.
     *
     * ```php
     * PostgresArray::encode(['php', 'laravel']); // '{php,laravel}'
     * PostgresArray::encode([[1, 2], [3]]);      // '{{1,2},{3}}'
     * PostgresArray::encode([]);                 // '{}'
     * ```
     *
     * @param array<array-key, mixed> $array
     *
     * @return string a PostgreSQL array literal
     *
     * Pitfall: elements are emitted unquoted, so a value containing `,`, `{`, `}` or `"`
     * round-trips incorrectly. Use a `jsonb` column for values that may contain them.
     */
    public static function encode(array $array): string
    {
        $json = json_encode(self::toIndexedArray($array), JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return '{}';
        }

        return str_replace(['[', ']', '"'], ['{', '}', ''], $json);
    }

    /**
     * Decode a PostgreSQL array literal into a PHP array.
     *
     * Numeric elements are cast: digit-only strings become `int`, other numerics become `float`.
     * Nested literals become nested arrays. Anything that does not start with `{` yields `[]`.
     *
     * ```php
     * PostgresArray::decode("{o'brien}");      // ["o'brien"] — `'` is not a quote character
     * PostgresArray::decode('{php,laravel}'); // ['php', 'laravel']
     * PostgresArray::decode('{1,2.5}');       // [1, 2.5]
     * PostgresArray::decode('{{1,2},{3}}');   // [[1, 2], [3]]
     * PostgresArray::decode(null);            // []
     * ```
     *
     * @param string|null $literal the raw column value
     * @param int $start offset to start parsing at (used for nested literals)
     * @param int|null $end set to the offset of the closing brace that ended this literal
     *
     * @return array<int, mixed>
     */
    public static function decode(?string $literal, int $start = 0, ?int &$end = null): array
    {
        if ($literal === null || $literal === '' || $literal[$start] !== '{') {
            return [];
        }

        $result   = [];
        $inString = false;
        $length   = strlen($literal);
        $value    = '';

        for ($i = $start + 1; $i < $length; $i++) {
            $char = $literal[$i];

            if (!$inString && $char === '}') {
                if ($value !== '' || $result !== []) {
                    $result[] = $value;
                }
                $end = $i;
                break;
            }

            if (!$inString && $char === '{') {
                $value = self::decode($literal, $i, $i);
            } elseif (!$inString && $char === ',') {
                $result[] = $value;
                $value    = '';
            } elseif (!$inString && $char === '"') {
                // PostgreSQL quotes array elements with double quotes only; a single quote
                // inside an element is an ordinary character. Verified against `array_out`:
                // `SELECT ARRAY['o''brien']::text[]` prints `{o'brien}`.
                $inString = true;
            } elseif ($inString && $char === '"') {
                if ($literal[$i - 1] === '\\') {
                    $value = substr($value, 0, -1) . $char;
                } else {
                    $inString = false;
                }
            } else {
                $value .= $char;
            }
        }

        foreach ($result as &$element) {
            if (is_numeric($element)) {
                $element = ctype_digit((string)$element) ? (int)$element : (float)$element;
            }
        }

        return $result;
    }

    /**
     * Drop string keys recursively, leaving a plain ordered list.
     *
     * ```php
     * PostgresArray::toIndexedArray(['a' => 1, 'b' => ['c' => 2]]); // [1, [2]]
     * ```
     *
     * @param array<array-key, mixed> $array
     *
     * @return array<int, mixed>
     */
    public static function toIndexedArray(array $array): array
    {
        $array = array_values($array);

        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::toIndexedArray($value);
            }
        }

        return $array;
    }
}
