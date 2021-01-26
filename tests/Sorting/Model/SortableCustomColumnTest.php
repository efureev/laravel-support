<?php

namespace Php\Support\Laravel\Tests\Sorting\Model;

use Illuminate\Support\Facades\DB;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\Models\SortCustomColumnModel;

class SortableCustomColumnTest extends AbstractTestCase
{
    protected $migrations = [
        'sortable/2021_01_21_092141_create_sortable_table_w_custom_col.php',
    ];

    public function testInsertZeroSortingPosition_incrementSortingPosition(): void
    {
        /** @var SortCustomColumnModel $model */
        $model = SortCustomColumnModel::make(['title' => 'test'])
            ->setSortingPosition(0);


        $model->save();
        $model->refresh();

        $this->assertEquals(1, $model->sortingPosition());
    }

    public function testInsert_Basic(): void
    {
        /** @var SortCustomColumnModel $model */
        $model = SortCustomColumnModel::create(['title' => 'test']);
        $this->assertEquals(1, $model->refresh()->sortingPosition());

        $model = SortCustomColumnModel::create(['title' => 'test']);
        $this->assertEquals(2, $model->refresh()->sortingPosition());

        $model = SortCustomColumnModel::make(['title' => 'test']);
        $model->save();
        $this->assertEquals(3, $model->refresh()->sortingPosition());

        $model = SortCustomColumnModel::make(['title' => 'test'])->setSortingPosition(0);
        $model->save();
        $this->assertEquals(4, $model->refresh()->sortingPosition());

        $model = SortCustomColumnModel::make(['title' => 'test'])->setSortingPosition(-2);
        $model->save();
        $this->assertEquals(5, $model->refresh()->sortingPosition());
    }

    public function testInsert_and_self_move(): void
    {
        /** @var SortCustomColumnModel $model */
        $model = SortCustomColumnModel::create(['title' => 'test']);
        $this->assertEquals(1, $model->refresh()->sortingPosition());

        $model->setSortingPosition(3)->save();
        $this->assertEquals(3, $model->refresh()->sortingPosition());

        $model->setSortingPosition(0)->save();

        $this->assertEquals(1, $model->refresh()->sortingPosition());
    }

    protected static function fillSimpleRawData(
        int $count = 4,
        bool $ordering = true,
        bool $orderingReverse = false
    ): void {
        $table = (new SortCustomColumnModel)->getTable();
        $spCol = SortCustomColumnModel::getSortingColumnName();

        for ($i = 1; $i <= $count; $i++) {
            $title = "test_$i";
            if ($ordering) {
                $orderID = $orderingReverse ? $count - $i + 1 : $i;

                DB::insert("insert into $table (title, {$spCol}) values (?,?)", [$title, $orderID]);
            } else {
                DB::insert("insert into $table (title) values (?)", [$title]);
            }
        }

        static::assertCount($count, SortCustomColumnModel::all());
    }
}
