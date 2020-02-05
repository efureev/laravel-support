<?php

namespace Php\Support\Laravel\Tests\Sorting\Model;

use Php\Support\Laravel\Sorting\Model\Sortable;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\Models\SortEntity;

class SortableTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations_sortable');
    }

    public function testInsertZeroSortingPosition_incrementSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = 0;
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(1, $sortEntity->sorting_position);
    }

    public function testInsertEmptyStringSortingPosition_incrementSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = '';
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(1, $sortEntity->sorting_position);
    }

    public function testInsertNullSortingPosition_incrementSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = null;
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(1, $sortEntity->sorting_position);
    }

    public function testInsertModelWithoutSortingPosition_throwsPDOException(): void
    {
        $this->expectException(\PDOException::class);

        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->save();
    }

    public function testUpdateZeroSortingPositionAfterNormal_setOldSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = 4;
        $sortEntity->save();
        $sortEntity->refresh();
        $sortEntity->sorting_position = 0;
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(4, $sortEntity->sorting_position);
    }

    public function testUpdateEmptyStringSortingPositionAfterNormal_setOldSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = 4;
        $sortEntity->save();
        $sortEntity->refresh();
        $sortEntity->sorting_position = '';
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(4, $sortEntity->sorting_position);
    }

    public function testUpdateNullSortingPositionAfterNormal_setOldSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = 4;
        $sortEntity->save();
        $sortEntity->refresh();
        $sortEntity->sorting_position = null;
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(4, $sortEntity->sorting_position);
    }

    public function testUpdateWithoutSortingPositionAfterNormal_setOldSortingPosition(): void
    {
        $sortEntity = new SortEntity();
        $sortEntity->title = 'test';
        $sortEntity->sorting_position = 4;
        $sortEntity->save();
        $sortEntity->refresh();
        $sortEntity->title = 'test32';
        $sortEntity->save();
        $sortEntity->refresh();

        $this->assertEquals(4, $sortEntity->sorting_position);
    }

    public function testGlobalScope_hasEntities_getTheirWithScopeOrder(): void
    {
        $sortEntity2 = new SortEntity();
        $sortEntity2->title = 'test';
        $sortEntity2->sorting_position = 2;
        $sortEntity2->save();

        $sortEntity3 = new SortEntity();
        $sortEntity3->title = 'test';
        $sortEntity3->sorting_position = 3;
        $sortEntity3->save();

        $sortEntity1 = new SortEntity();
        $sortEntity1->title = 'test';
        $sortEntity1->sorting_position = 1;
        $sortEntity1->save();

        $sortEntity4 = new SortEntity();
        $sortEntity4->title = 'test';
        $sortEntity4->sorting_position = 4;
        $sortEntity4->save();

        $actualEntities = SortEntity::all();

        $this->assertEquals($sortEntity1->id, $actualEntities[0]->id);
        $this->assertEquals($sortEntity2->id, $actualEntities[1]->id);
        $this->assertEquals($sortEntity3->id, $actualEntities[2]->id);
        $this->assertEquals($sortEntity4->id, $actualEntities[3]->id);
    }

    public function testGlobalScope_hasEntities_getTheirWithoutScopeOrder(): void
    {
        $sortEntity2 = new SortEntity();
        $sortEntity2->title = 'test';
        $sortEntity2->sorting_position = 2;
        $sortEntity2->save();

        $sortEntity3 = new SortEntity();
        $sortEntity3->title = 'test';
        $sortEntity3->sorting_position = 3;
        $sortEntity3->save();

        $sortEntity4 = new SortEntity();
        $sortEntity4->title = 'test';
        $sortEntity4->sorting_position = 4;
        $sortEntity4->save();

        $sortEntity1 = new SortEntity();
        $sortEntity1->title = 'test';
        $sortEntity1->sorting_position = 1;
        $sortEntity1->save();

        $actualEntities = SortEntity::withoutGlobalScope(SortEntity::getSortingScopeName())->get();

        $this->assertEquals($sortEntity1->id, $actualEntities[3]->id);
        $this->assertEquals($sortEntity2->id, $actualEntities[0]->id);
        $this->assertEquals($sortEntity3->id, $actualEntities[1]->id);
        $this->assertEquals($sortEntity4->id, $actualEntities[2]->id);
    }

    public function testReorderBySortingPosition_hasOldPositionMoreThanNew_getEntitiesWithReorder(): void
    {
        $sortEntity2 = new SortEntity();
        $sortEntity2->title = 'test2';
        $sortEntity2->sorting_position = 2;
        $sortEntity2->save();

        $sortEntity3 = new SortEntity();
        $sortEntity3->title = 'test3';
        $sortEntity3->sorting_position = 3;
        $sortEntity3->save();

        $sortEntity4 = new SortEntity();
        $sortEntity4->title = 'test4';
        $sortEntity4->sorting_position = 4;
        $sortEntity4->save();

        $sortEntity1 = new SortEntity();
        $sortEntity1->title = 'test1';
        $sortEntity1->sorting_position = 1;
        $sortEntity1->save();

        //reorder
        $sortEntity3->sorting_position = 2;
        $sortEntity3->save();
        $sortEntity3->refresh();

        $actualEntities = SortEntity::all();
        
        $this->assertEquals($sortEntity1->id, $actualEntities[0]->id);
        $this->assertEquals($sortEntity2->id, $actualEntities[2]->id);
        $this->assertEquals($sortEntity3->id, $actualEntities[1]->id);
        $this->assertEquals($sortEntity4->id, $actualEntities[3]->id);
    }

    public function testReorderBySortingPosition_hasOldPositionLessThanNew_getEntitiesWithReorder(): void
    {
        $sortEntity2 = new SortEntity();
        $sortEntity2->title = 'test2';
        $sortEntity2->sorting_position = 2;
        $sortEntity2->save();

        $sortEntity3 = new SortEntity();
        $sortEntity3->title = 'test3';
        $sortEntity3->sorting_position = 3;
        $sortEntity3->save();

        $sortEntity4 = new SortEntity();
        $sortEntity4->title = 'test4';
        $sortEntity4->sorting_position = 4;
        $sortEntity4->save();

        $sortEntity1 = new SortEntity();
        $sortEntity1->title = 'test1';
        $sortEntity1->sorting_position = 1;
        $sortEntity1->save();

        //reorder
        $sortEntity1->sorting_position = 4;
        $sortEntity1->save();
        $sortEntity1->refresh();

        $actualEntities = SortEntity::all();
        
        $this->assertEquals($sortEntity1->id, $actualEntities[3]->id);
        $this->assertEquals($sortEntity2->id, $actualEntities[0]->id);
        $this->assertEquals($sortEntity3->id, $actualEntities[1]->id);
        $this->assertEquals($sortEntity4->id, $actualEntities[2]->id);
    }
}
