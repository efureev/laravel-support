<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Caster\HasCasts;
use Php\Support\Laravel\Tests\TestClasses\Entity\ArrayCollection;
use Php\Support\Laravel\Tests\TestClasses\Entity\ComponentCollection;

/**
 * Class CasterDirtyCollection
 * @package Php\Support\Laravel\Tests\Models
 * @property ComponentCollection $components
 * @property ArrayCollection $arrays
 *
 * @mixin Builder
 */
class CasterDirtyCollection extends Model
{
    use HasCasts;

    public $timestamps = false;
    protected $keyType = 'string';
    protected $table = 'test_table_caster';

    protected $casts = [
        'components' => ComponentCollection::class,
        'arrays'     => ArrayCollection::class,
    ];

    protected $fillable = [
        'components',
        'arrays',
    ];
}
