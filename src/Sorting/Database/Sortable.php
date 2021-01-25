<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Sorting\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

trait Sortable
{
    /**
     * @param Blueprint $table
     *
     * @param string $columnName
     *
     * @return ColumnDefinition
     */
    protected static function columnSortingPosition(
        Blueprint $table,
        string $columnName = 'sorting_position'
    ): ColumnDefinition {
        return $table->unsignedInteger($columnName)->default(0)->index();
    }
}
