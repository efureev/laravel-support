<?php


namespace Php\Support\Laravel\Tests\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Model\Sortable;

class SortEntity extends Model
{
    use Sortable;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $keyType = 'uuid';
    
}
