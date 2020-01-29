<?php

namespace Php\Support\Laravel\Tests\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Caster\HasCasts;

/**
 * Class TestModel
 * @package Php\Support\Laravel\Tests\Models
 * @property boolean $enabled
 * @property string $title
 * @property Params $params
 * @property array $config
 * @property string $str
 * @property string $str_empty
 * @property int $int
 * @mixin Builder
 */
class TestModel extends Model
{
    use HasCasts;

    public $timestamps = false;
    protected $keyType = 'uuid';
    protected $table = 'test_table';

    protected $fillable = ['params', 'config', 'str', 'str_empty', 'int'];

    protected $casts = [
        'enabled'   => 'bool',
        'params'    => Params::class,
        'config'    => 'array',
        'str'       => 'string',
        'str_empty' => null,
        'int'       => 'int',
    ];

}
