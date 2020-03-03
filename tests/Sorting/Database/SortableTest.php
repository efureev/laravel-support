<?php

namespace Php\Support\Laravel\Tests\Sorting\Database;

use Illuminate\Database\Schema\Blueprint;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Php\Support\Laravel\Sorting\Enum;
use Php\Support\Laravel\Tests\AbstractTestCase;

class SortableTest extends AbstractTestCase
{
    use Sortable;

    public function testColumnSortingPosition_addColumnWithNeedleAttributes(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations_sortable');
        $table = new Blueprint('test');

        static::columnSortingPosition($table);

        $attributes = $table->getColumns()[0]->getAttributes();

        $this->assertEquals('integer', $attributes['type']);
        $this->assertEquals(Enum::SORTING_POSITION_COLUMN, $attributes['name']);
        $this->assertFalse($attributes['autoIncrement']);
        $this->assertTrue($attributes['unsigned']);
        $this->assertTrue($attributes['index']);
    }
}
