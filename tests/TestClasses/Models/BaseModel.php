<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

/**
 * Class TestModel
 * @package Php\Support\Laravel\Tests\Models
 * @property boolean $enabled
 * @property string $title
 * @property string $name
 * @property array<string, mixed> $config
 * @property string $str
 * @property string $str_empty
 * @property int $int
 * @property User $user
 * @mixin Builder<BaseModel>
 */
class BaseModel extends Model
{
    public $timestamps = false;

    protected $table = 'base_table';

    protected $fillable = ['name'];
}
