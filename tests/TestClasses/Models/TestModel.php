<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

/**
 * Class TestModel
 * @package Php\Support\Laravel\Tests\Models
 * @property boolean $enabled
 * @property string $title
 * @property array<string, mixed> $config
 * @property string $str
 * @property string $str_empty
 * @property int $int
 * @property User $user
 * @mixin Builder<TestModel>
 */
class TestModel extends Model
{
    public $timestamps = false;
    protected $keyType = 'string';
    protected $table   = 'test_table';

    protected $fillable = [
        'config',
        'str',
        'str_empty',
        'int',
        'enabled',
    ];

    protected $casts = [
        'enabled'   => 'bool',
        'config'    => 'array',
        'str'       => 'string',
        'str_empty' => 'string',
        'int'       => 'int',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
