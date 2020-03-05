<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Sorting\Database;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

trait Sortable
{
    /**
     * @param Blueprint $table
     *
     * @return ColumnDefinition
     * @throws Exception
     */
    protected static function columnSortingPosition(
        Blueprint $table
    ): ColumnDefinition {
        return $table->unsignedInteger('sorting_position')->nullable(false)->index();
    }
}
