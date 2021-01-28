<?php

namespace Php\Support\Laravel\Tests\Sorting\Model;

use Illuminate\Support\Facades\DB;
use Php\Support\Laravel\Sorting\Model\SortOrderingAsc;
use Php\Support\Laravel\Sorting\Model\SortOrderingDesc;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\Models\SortEntity;

class SortableTest extends AbstractTestCase
{
    protected $migrations = [
        'sortable/2020_02_04_075141_create_sortable_table.php',
    ];

    protected static function fillSimpleRawData(
        int $count = 4,
        bool $ordering = true,
        bool $orderingReverse = false
    ): void {
        $table = (new SortEntity)->getTable();
        $spCol = SortEntity::getSortingColumnName();

        for ($i = 1; $i <= $count; $i++) {
            $title = "test_$i";
            if ($ordering) {
                $orderID = $orderingReverse ? $count - $i + 1 : $i;

                DB::insert("insert into $table (title, {$spCol}) values (?,?)", [$title, $orderID]);
            } else {
                DB::insert("insert into $table (title) values (?)", [$title]);
            }
        }

        static::assertCount($count, SortEntity::all());
    }

    protected static function wipeData(): void
    {
        $table = (new SortEntity)->getTable();
        DB::delete("delete from $table");

        static::assertCount(0, SortEntity::all());
    }

    public function testInsert_Basic(): void
    {
        /** @var SortEntity $model */
        $model = SortEntity::create(['title' => 'test']);
        $this->assertEquals(1, $model->refresh()->sortingPosition());

        $model = SortEntity::create(['title' => 'test']);
        $this->assertEquals(2, $model->refresh()->sortingPosition());

        $model = SortEntity::make(['title' => 'test']);
        $model->save();
        $this->assertEquals(3, $model->refresh()->sortingPosition());

        $model = SortEntity::make(['title' => 'test'])->setSortingPosition(0);
        $model->save();
        $this->assertEquals(4, $model->refresh()->sortingPosition());

        $model = SortEntity::make(['title' => 'test'])->setSortingPosition(-2);
        $model->save();
        $this->assertEquals(5, $model->refresh()->sortingPosition());
    }

    public function testInsert_and_self_move(): void
    {
        /** @var SortEntity $model */
        $model = SortEntity::create(['title' => 'test']);
        $this->assertEquals(1, $model->refresh()->sortingPosition());

        $model->setSortingPosition(3)->save();
        $this->assertEquals(3, $model->refresh()->sortingPosition());

        $model->setSortingPosition(0)->save();

        $this->assertEquals(1, $model->refresh()->sortingPosition());
    }

    public function testInsert_and_move(): void
    {
        /** @var SortEntity $model1 */
        $model1 = SortEntity::create(['title' => 'test']);
        $this->assertEquals(1, $model1->refresh()->sortingPosition());

        /** @var SortEntity $model2 */
        $model2 = SortEntity::make(['title' => 'test4'])->setSortingPosition(4);
        $model2->save();
        $this->assertEquals(4, $model2->refresh()->sortingPosition());

        $model2->setSortingPosition(0)->save();
        $this->assertEquals(2, $model2->refresh()->sortingPosition());

        $model2->setSortingPosition(6)->save();
        $this->assertEquals(6, $model2->refresh()->sortingPosition());

        $model2->setSortingPosition(0)->save();
        $this->assertEquals(2, $model2->refresh()->sortingPosition());

        $model2->setSortingPosition(6)->save();
        $this->assertEquals(6, $model2->refresh()->sortingPosition());

        $model1->setSortingPosition(0)->save();
        $this->assertEquals(7, $model1->refresh()->sortingPosition());
    }

    /**
     * В существующий стек добавить новую запись с sortingPosition == 0
     */
    public function testAddToStack_AddSimpleNew(): void
    {
        static::fillSimpleRawData();

        /** @var SortEntity $model */
        $model = SortEntity::create(['title' => 'new_1']);
        $this->assertEquals(5, $model->refresh()->sortingPosition());

        /** @var SortEntity $model */
        $model = SortEntity::make(['title' => 'new_2'])->setSortingPosition(0);
        $model->save();
        $this->assertEquals(6, $model->refresh()->sortingPosition());
    }

    /**
     * В существующий стек добавить новую запись с sortingPosition > max(существующих)
     */
    public function testAddToStack_AddNewWithSortingPositionGreaterThenExisted(): void
    {
        static::fillSimpleRawData();

        /** @var SortEntity $model */
        $model = SortEntity::make(['title' => 'new_1'])->setSortingPosition(8);
        $model->save();
        $this->assertEquals(8, $model->refresh()->sortingPosition());

        /** @var SortEntity $model */
        $model = SortEntity::make(['title' => 'new_2'])->setSortingPosition(19);
        $model->save();
        $this->assertEquals(19, $model->refresh()->sortingPosition());
    }

    /**
     * В существующий стек добавить новую запись с sortingPosition >= min & <= max от существующих
     */
    public function testAddToStack_AddNewWithSortingPositionLessThenMax(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            static::wipeData();
            static::fillSimpleRawData();

            $this->addToStack_AddNewWithSortingPosition_base($i);
        }
    }

    private function addToStack_AddNewWithSortingPosition_base($position): void
    {
        /** @var SortEntity $model */
        $model = SortEntity::make(['title' => "expected_$position"])->setSortingPosition($position);

        $expectedModels = SortEntity::sortingPositionGreaterThen($position)
            ->pluck(SortEntity::getSortingColumnName(), 'id')
            ->all();

        array_walk(
            $expectedModels,
            static function (&$item) {
                return $item++;
            }
        );

        $model->save();
        $this->assertEquals($position, $model->refresh()->sortingPosition());

        $actualModels = SortEntity::sortingPositionGreaterThen($position, false)
            ->pluck(SortEntity::getSortingColumnName(), 'id')
            ->all();

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expectedModels, JSON_THROW_ON_ERROR, 512),
            \json_encode($actualModels, JSON_THROW_ON_ERROR, 512)
        );
    }

    public function testAddToStack_AddNewWithSortingPositionLessOrEqualZero(): void
    {
        foreach ([-32, -1000, 0, -1] as $position) {
            static::wipeData();
            static::fillSimpleRawData();

            $this->addToStack_AddNewWithSortingPosition_baseZero($position);
        }
    }

    private function addToStack_AddNewWithSortingPosition_baseZero($position): void
    {
        /** @var SortEntity $model */
        $model = SortEntity::make(['title' => 'expected_1'])->setSortingPosition($position);

        $expectedModels = SortEntity::sortingPositionGreaterThen(1)
            ->pluck(SortEntity::getSortingColumnName(), 'id')
            ->all();

        $model->save();
        $this->assertEquals(5, $model->refresh()->sortingPosition());

        $actualModels = SortEntity::sortingPositionLessThen(5, false)
            ->pluck(SortEntity::getSortingColumnName(), 'id')
            ->all();

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expectedModels, JSON_THROW_ON_ERROR, 512),
            \json_encode($actualModels, JSON_THROW_ON_ERROR, 512)
        );
    }

    public function testAddToStack_AddNewWithSortingPosition_setFirstForSortingPosition(): void
    {
        static::fillSimpleRawData();

        /** @var SortEntity $model */
        $model = SortEntity::make(['title' => 'expected_max'])->setFirstForSortingPosition();

        $expectedModels = SortEntity::sortingPositionGreaterThen(1)
            ->pluck(SortEntity::getSortingColumnName(), 'id')
            ->all();

        array_walk(
            $expectedModels,
            static function (&$item) {
                return $item++;
            }
        );

        $model->save();
        $this->assertEquals(1, $model->refresh()->sortingPosition());

        $actualModels = SortEntity::sortingPositionGreaterThen(1, false)
            ->pluck(SortEntity::getSortingColumnName(), 'id')
            ->all();

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expectedModels, JSON_THROW_ON_ERROR, 512),
            \json_encode($actualModels, JSON_THROW_ON_ERROR, 512)
        );
    }

    /**
     * Передвижение уже существующих моделей. Передвижение вниз: 4 => 2
     */
    public function testMoveWithinStack_decreaseSortingPosition(): void
    {
        static::fillSimpleRawData(5);

        $model1 = SortEntity::find(1);
        $model2 = SortEntity::find(2);
        $model3 = SortEntity::find(3);
        $model4 = SortEntity::find(4);
        $model5 = SortEntity::find(5);

        $this->assertEquals(1, $model1->sortingPosition());
        $this->assertEquals(2, $model2->sortingPosition());
        $this->assertEquals(3, $model3->sortingPosition());
        $this->assertEquals(4, $model4->sortingPosition());
        $this->assertEquals(5, $model5->sortingPosition());

        /** @var SortEntity $model4 */
        $model4->setSortingPosition(2)->save();
        $this->assertEquals(2, $model4->refresh()->sortingPosition());

        //        dd(SortEntity::all()->toArray());
        $this->assertEquals(1, $model1->refresh()->sortingPosition());
        $this->assertEquals(3, $model2->refresh()->sortingPosition());
        $this->assertEquals(4, $model3->refresh()->sortingPosition());
        $this->assertEquals(5, $model5->refresh()->sortingPosition());
    }


    /**
     * Передвижение уже существующих моделей. Передвижение вниз: 2 => 4
     */
    public function testMoveWithinStack_increaseSortingPosition(): void
    {
        static::fillSimpleRawData(5);

        $model1 = SortEntity::find(1);
        $model2 = SortEntity::find(2);
        $model3 = SortEntity::find(3);
        $model4 = SortEntity::find(4);
        $model5 = SortEntity::find(5);

        $this->assertEquals(1, $model1->sortingPosition());
        $this->assertEquals(2, $model2->sortingPosition());
        $this->assertEquals(3, $model3->sortingPosition());
        $this->assertEquals(4, $model4->sortingPosition());
        $this->assertEquals(5, $model5->sortingPosition());

        /** @var SortEntity $model2 */
        $model2->setSortingPosition(4)->save();
        $this->assertEquals(4, $model2->refresh()->sortingPosition());

        $this->assertEquals(1, $model1->refresh()->sortingPosition());
        $this->assertEquals(2, $model3->refresh()->sortingPosition());
        $this->assertEquals(3, $model4->refresh()->sortingPosition());
        $this->assertEquals(5, $model5->refresh()->sortingPosition());
    }

    public function testOrderingBySortingPosition(): void
    {
        static::fillSimpleRawData(10);

        static::assertArrayNotHasKey(
            SortEntity::getSortingScopeName(),
            SortEntity::query()->getModel()->getGlobalScopes()
        );

        $models = SortEntity::pluck(SortEntity::getSortingColumnName());
        $count  = $models->count();

        foreach ($models as $key => $sp) {
            //            static::assertEquals($id, $count - $sp + 1);
            static::assertEquals($key + 1, $sp);
        }
    }

    public function testOrderingByDescSortingPositionWithGlobalScope(): void
    {
        static::fillSimpleRawData(10);
        SortEntity::addGlobalScope(new SortOrderingDesc());

        static::assertArrayHasKey(
            SortOrderingDesc::class,
            SortEntity::query()->getModel()->getGlobalScopes()
        );

        $models = SortEntity::pluck(SortEntity::getSortingColumnName());
        $count  = $models->count();

        foreach ($models as $key => $sp) {
            static::assertEquals($key + 1, $count - $sp + 1);
        }
    }


    public function testOrderingByAscSortingPosition(): void
    {
        static::fillSimpleRawData(10);

        static::assertArrayNotHasKey(
            SortEntity::getSortingScopeName(),
            SortEntity::query()->getModel()->getGlobalScopes()
        );

        $models = SortEntity::sortingPositionOrderByAsc()->pluck(SortEntity::getSortingColumnName());

        foreach ($models as $key => $sp) {
            //            static::assertEquals($id, $count - $sp + 1);
            static::assertEquals($key + 1, $sp);
        }
    }

    public function testOrderingByDescSortingPosition(): void
    {
        static::fillSimpleRawData(10);

        static::assertArrayNotHasKey(
            SortEntity::getSortingScopeName(),
            SortEntity::query()->getModel()->getGlobalScopes()
        );

        $models = SortEntity::sortingPositionOrderByDesc()->pluck(SortEntity::getSortingColumnName());
        $count  = $models->count();

        foreach ($models as $key => $sp) {
            static::assertEquals($key + 1, $count - $sp + 1);
        }
    }

    public function testOrderingByAscSortingPositionWithGlobalScope(): void
    {
        static::fillSimpleRawData(10);
        SortEntity::addGlobalScope(new SortOrderingAsc());

        static::assertArrayHasKey(
            SortOrderingAsc::class,
            SortEntity::query()->getModel()->getGlobalScopes()
        );

        $models = SortEntity::pluck(SortEntity::getSortingColumnName());
        $count  = $models->count();

        foreach ($models as $key => $sp) {
            static::assertEquals($key + 1, $sp);
        }
    }
}
