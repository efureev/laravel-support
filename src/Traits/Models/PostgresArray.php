<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Php\Support\Laravel\Helpers\PostgresArray as PgArray;
use InvalidArgumentException;
use RuntimeException;

/**
 * Query scopes for native PostgreSQL array columns (`text[]`, `integer[]`, …).
 *
 * Pair it with {@see \Php\Support\Laravel\Casts\PostgresArrayCast} so the attribute is a PHP
 * array on the way in and out.
 *
 * @see https://www.postgresql.org/docs/current/functions-array.html
 *
 * @method static int pgArrayAppend(string $column, array<array-key, scalar> $values, bool $unique = false)
 * @method static int pgArrayRemove(string $column, array<array-key, scalar> $values)
 *
 * @mixin Model
 */
trait PostgresArray
{
    /**
     * Rows whose `$column` contains **every** element of `$value` (PostgreSQL `@>`).
     *
     * A `Closure` may be passed to supply a raw array literal; it must return a string.
     *
     * ```php
     * Post::wherePgArrayContains('tags', ['php', 'laravel'])->get();
     * Post::wherePgArrayContains('tags', fn() => '{php,laravel}')->get();
     * ```
     *
     * @param Builder<static> $query
     * @param mixed $value a scalar, a set, or a Closure returning a raw array literal
     *
     * @return Builder<static>
     *
     * @throws RuntimeException when the closure does not return a string
     */
    public function scopeWherePgArrayContains(Builder $query, string $column, mixed $value): Builder
    {
        if ($value instanceof Closure) {
            $value = $value();

            if (!is_string($value)) {
                throw new RuntimeException('Result`s value must have STRING type!');
            }
        } else {
            $value = PgArray::encode((array)$value);
        }

        return $query->whereRaw(static::wrapSortableColumn($query, $column) . ' @> ?', [$value]);
    }

    /**
     * Rows whose `$column` contains the single element `$value` (PostgreSQL `= ANY`).
     *
     * ```php
     * Post::wherePgArrayContainsAny('tags', 'php')->get();
     * ```
     *
     * @param Builder<static> $query
     * @param mixed $value a single element; a set raises RuntimeException — use
     *                     {@see self::scopeWherePgArrayOverlapWith()} for that
     *
     * @return Builder<static>
     */
    public function scopeWherePgArrayContainsAny(Builder $query, string $column, mixed $value): Builder
    {
        static::assertScalarElement($value, __FUNCTION__);

        return $query->whereRaw('? = ANY (' . static::wrapSortableColumn($query, $column) . ')', [$value]);
    }

    /**
     * Rows whose `$column` consists of nothing but `$value` (PostgreSQL `= ALL`).
     *
     * ```php
     * Post::wherePgArrayContainsOnly('tags', 'php')->get();
     * ```
     *
     * Pitfall: `ALL` yields true when the array has **zero** elements, so empty arrays match.
     * Add `->whereRaw("array_length($column, 1) > 0")` when that is not what you want.
     *
     * @param Builder<static> $query
     * @param mixed $value a single element; a set raises RuntimeException
     *
     * @return Builder<static>
     */
    public function scopeWherePgArrayContainsOnly(Builder $query, string $column, mixed $value): Builder
    {
        static::assertScalarElement($value, __FUNCTION__);

        return $query->whereRaw('? = ALL (' . static::wrapSortableColumn($query, $column) . ')', [$value]);
    }

    /**
     * Rows whose `$column` shares at least one element with `$value` (PostgreSQL `&&`).
     *
     * ```php
     * Post::wherePgArrayOverlapWith('tags', ['php', 'python'])->get();
     * ```
     *
     * @param Builder<static> $query
     * @param array<array-key, scalar> $value
     *
     * @return Builder<static>
     */
    public function scopeWherePgArrayOverlapWith(Builder $query, string $column, array $value): Builder
    {
        return $query->whereRaw(static::wrapSortableColumn($query, $column) . ' && ?', [PgArray::encode($value)]);
    }

    /**
     * Append `$values` to a native array column for every row the query matches, in one
     * statement (PostgreSQL `array_cat`).
     *
     * Duplicates are kept — a PostgreSQL array is an ordered list, not a set. Pass
     * `unique: true` to collapse them, at the cost of losing the original order.
     *
     * ```php
     * Post::query()->whereKey($id)->pgArrayAppend('tags', ['php']);
     * ```
     *
     * @see https://www.postgresql.org/docs/current/functions-array.html
     *
     * @param Builder<static> $query
     * @param array<array-key, scalar> $values
     *
     * @return int the number of rows updated
     */
    public function scopePgArrayAppend(
        Builder $query,
        string $column,
        array $values,
        bool $unique = false
    ): int {
        if ($values === []) {
            return 0;
        }

        $wrapped = static::wrapSortableColumn($query, $column);
        $literal = static::quoteLiteral($query, PgArray::encode($values));

        $sql = $unique
            ? "(SELECT array_agg(DISTINCT e) FROM unnest(array_cat(COALESCE($wrapped, '{}'), $literal::text[])) AS e)"
            : "array_cat(COALESCE($wrapped, '{}'), $literal::text[])";

        return $query->toBase()->update([$column => new Expression($sql)]);
    }

    /**
     * Remove every occurrence of each of `$values` from a native array column
     * (PostgreSQL `array_remove`, applied once per value).
     *
     * ```php
     * Post::query()->whereKey($id)->pgArrayRemove('tags', ['php']);
     * ```
     *
     * @param Builder<static> $query
     * @param array<array-key, scalar> $values
     *
     * @return int the number of rows updated
     */
    public function scopePgArrayRemove(Builder $query, string $column, array $values): int
    {
        if ($values === []) {
            return 0;
        }

        $sql = static::wrapSortableColumn($query, $column);

        foreach ($values as $value) {
            $quoted = static::quoteLiteral($query, (string)$value);
            $sql    = "array_remove($sql, $quoted)";
        }

        return $query->toBase()->update([$column => new Expression($sql)]);
    }

    /**
     * Quote a value as a SQL string literal.
     *
     * `Builder::update()` inlines `Expression` values and carries no bindings for them, so the
     * literal has to be escaped here. The driver does it, not a hand-rolled `str_replace`.
     *
     * @param Builder<static> $query
     */
    protected static function quoteLiteral(Builder $query, string $value): string
    {
        $connection = $query->getConnection();

        if (!$connection instanceof Connection) {
            throw new RuntimeException(
                sprintf('A PDO connection is required to quote array literals, got %s.', $connection::class)
            );
        }

        return $connection->getPdo()->quote($value);
    }

    /**
     * Quote `$column` as an identifier before it goes into raw SQL.
     *
     * The column name is a caller-supplied string that cannot be bound as a parameter, so it is
     * validated as a plain (optionally table-qualified) identifier and then wrapped by the
     * connection's grammar.
     *
     * @param Builder<static> $query
     *
     * @throws InvalidArgumentException when the name is not a bare identifier
     *
     * @see https://laravel.com/docs/13.x/queries#raw-expressions
     */
    protected static function wrapSortableColumn(Builder $query, string $column): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $column)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid column name.', $column)
            );
        }

        return $query->getQuery()->getGrammar()->wrap($column);
    }

    /**
     * @throws RuntimeException when a set is passed where a single element is expected
     */
    protected static function assertScalarElement(mixed $value, string $scope): void
    {
        if (!is_scalar($value)) {
            throw new RuntimeException(
                sprintf(
                    '%s() compares a single element, got %s. Use wherePgArrayOverlapWith() for a set.',
                    $scope,
                    get_debug_type($value)
                )
            );
        }
    }
}
