<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Sorting\Database;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Php\Support\Laravel\Sorting\Enum;

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
        return $table->unsignedInteger(Enum::SORTING_POSITION_COLUMN)->nullable(false)->index();
    }
}
