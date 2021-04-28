<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Sorting\Model\Sortable;

/**
 * Class SortEntity
 * @package Php\Support\Laravel\Tests\TestClasses\Models
 * @property string $id
 * @property string $title
 */
class SortEntity extends Model
{
    use Sortable;

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = ['title'];
}
