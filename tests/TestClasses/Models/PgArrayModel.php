<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Caster\HasCasts;
use Php\Support\Laravel\Caster\PgArray;
use Php\Support\Laravel\Traits\Models\PostgresArray;

/**
 * Class PgModel
 * @package Php\Support\Laravel\Tests\Models
 *
 * @property string $title
 * @property array $tags
 * @property array $tag_ids
 * @method PgArrayModel byTag(string $tag)
 *
 * @mixin Builder
 */
class PgArrayModel extends Model
{
    use HasCasts;
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
        'tags' => PgArray::class,
        //        'tag_ids' => PgArray::class,
    ];

    public function scopeByTag(Builder $query, string $value)
    {
        $this->scopeWherePgArrayContains($query, 'tags', $value);
    }
}
