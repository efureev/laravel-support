<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Caster\HasCasts;
use Php\Support\Laravel\Tests\TestClasses\Entity\Params;

/**
 * Class TestDirtyModel
 * @package Php\Support\Laravel\Tests\Models
 * @property Params $params
 *
 * @mixin Builder
 */
class TestDirtyModel extends Model
{
    use HasCasts;

    public $timestamps = false;
    protected $keyType = 'uuid';
    protected $table = 'test_table';

    protected $fillable = [
        'params',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'params'  => Params::class,
    ];
}
