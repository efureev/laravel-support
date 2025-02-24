<?php

namespace Php\Support\Laravel\Tests\Sorting\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\Models\SortCustomColumnModel;

class SortableTest extends AbstractTestCase
{
    use Sortable;

    public function testColumnSortingPosition_addColumnWithNeedleAttributes(): void
    {
        /** @var Connection $conn */
        $conn = $this->app->get('db.connection');

        $table = new Blueprint($conn, 'test');

        static::columnSortingPosition($table);

        $attributes = $table->getColumns()[0]->getAttributes();

        $this->assertEquals('integer', $attributes['type']);
        $this->assertEquals('sorting_position', $attributes['name']);
        $this->assertFalse($attributes['autoIncrement']);
        $this->assertTrue($attributes['unsigned']);
        $this->assertTrue($attributes['index']);
    }

    public function testColumnSortingPosition_withCustomColumnName(): void
    {
        /** @var Connection $conn */
        $conn = $this->app->get('db.connection');

        $table = new Blueprint($conn, 'test2');

        static::columnSortingPosition($table, SortCustomColumnModel::getSortingColumnName());

        $attributes = $table->getColumns()[0]->getAttributes();

        $this->assertEquals('integer', $attributes['type']);
        $this->assertEquals(SortCustomColumnModel::getSortingColumnName(), $attributes['name']);
        $this->assertFalse($attributes['autoIncrement']);
        $this->assertTrue($attributes['unsigned']);
        $this->assertTrue($attributes['index']);
    }
}
