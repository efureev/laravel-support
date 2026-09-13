<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Casts\PostgresArrayCast;
use Php\Support\Laravel\Traits\Models\PostgresArray;

/**
 * Class PgModel
 * @package Php\Support\Laravel\Tests\Models
 *
 * @property string $title
 * @property array<int, mixed> $tags
 * @property array<int, mixed> $tag_ids
 * @method PgArrayModel byTag(string $tag)
 *
 * @mixin Builder<PgArrayModel>
 */
class PgArrayModel extends Model
{
    use PostgresArray;

    public $timestamps = false;
    protected $keyType = 'string';
    protected $table   = 'pg_table';

    protected $fillable = [
        'title',
        'tags',
        'tag_ids',
    ];

    protected $casts = [
        'tags'    => PostgresArrayCast::class,
        'tag_ids' => PostgresArrayCast::class,
    ];

    /**
     * @param Builder<static> $query
     */
    public function scopeByTag(Builder $query, string $value): void
    {
        $this->scopeWherePgArrayContains($query, 'tags', $value);
    }
}
