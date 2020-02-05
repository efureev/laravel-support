<?php


namespace Php\Support\Laravel\Sorting\Database;

use Exception;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

trait Sortable
{
    /**
     * @param Blueprint $table
     * @param string $name
     * @param bool|callable|Expression $default
     *
     * @return ColumnDefinition
     * @throws Exception
     */
    protected static function columnSortingPosition(
        Blueprint $table,
        string $name = 'sorting_position'
    ): ColumnDefinition {
        return $table->integer($name)->unique();
    }
}
