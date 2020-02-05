<?php

namespace Php\Support\Laravel\Tests\Sorting\Database;

use Illuminate\Database\Schema\Blueprint;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Php\Support\Laravel\Tests\AbstractTestCase;

class SortableTest extends AbstractTestCase
{
    use Sortable;

    public function testColumnSortingPosition_hasName_addColumnWithNeedleAttributes(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations_sortable');
        $table = new Blueprint('test');
        
        static::columnSortingPosition($table, 'test_sortable');

        $attributes = $table->getColumns()[0]->getAttributes();
        $this->assertEquals('integer', $attributes['type']);
        $this->assertEquals('test_sortable', $attributes['name']);
        $this->assertFalse($attributes['autoIncrement']);
        $this->assertFalse($attributes['unsigned']);
        $this->assertTrue($attributes['index']);
    }

    public function testColumnSortingPosition_hasNotName_addColumnWithNeedleAttributes(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations_sortable');
        $table = new Blueprint('test');

        static::columnSortingPosition($table);

        $attributes = $table->getColumns()[0]->getAttributes();

        $this->assertEquals('integer', $attributes['type']);
        $this->assertEquals('sorting_position', $attributes['name']);
        $this->assertFalse($attributes['autoIncrement']);
        $this->assertFalse($attributes['unsigned']);
        $this->assertTrue($attributes['index']);
    }
}
