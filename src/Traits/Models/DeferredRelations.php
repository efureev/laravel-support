<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait DeferredRelations
{
    protected static array $deferredRelations = [];

    public static function addDeferredRelation(string $relation, mixed $callback): void
    {
        self::$deferredRelations[$relation] = $callback;
    }

    public static function getDeferredRelation(string $relation): mixed
    {
        return self::$deferredRelations[$relation];
    }

    public static function hasDeferredRelation(string $relation): bool
    {
        return isset(self::$deferredRelations[$relation]);
    }

    public static function getDeferredRelations(): array
    {
        return self::$deferredRelations;
    }

    public static function unsetDeferredRelation($relation): void
    {
        unset(self::$deferredRelations[$relation]);
    }

    public static function unsetDeferredRelations(): void
    {
        self::$deferredRelations = [];
    }
}
