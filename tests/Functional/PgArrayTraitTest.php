<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Php\Support\Laravel\Tests\TestClasses\Models\PgArrayModel;

class PgArrayTraitTest extends AbstractFunctionalTestCase
{
    public function testCreateAndGetWithNullTags(): void
    {
        /** @var PgArrayModel $model */
        $model = PgArrayModel::create();

        static::assertInstanceOf(PgArrayModel::class, $model);
        static::assertNull($model->title);
        static::assertNull($model->tags);
        static::assertNull($model->getOriginal('tags'));
    }

    public function testCreateAndGetWithEmptyArrayTags(): void
    {
        /** @var PgArrayModel $model */
        $model = PgArrayModel::create(['title' => 'test title', 'tags' => []]);

        static::assertInstanceOf(PgArrayModel::class, $model);
        static::assertEquals('{}', $model->getRawOriginal('tags'));
        static::assertEquals('test title', $model->title);
        static::assertEquals([], $model->tags);
    }

    public function testCreateAndGetWithFillArrayTags(): void
    {
        /** @var PgArrayModel $model */
        $model = PgArrayModel::create(['title' => 'test title', 'tags' => ['key', 'key2', '2']]);

        static::assertInstanceOf(PgArrayModel::class, $model);
        static::assertEquals('test title', $model->title);
        static::assertEquals(['key', 'key2', '2'], $model->tags);
        static::assertEquals('{key,key2,2}', $model->getRawOriginal('tags'));
    }

    public function testCreateAndGetWithFillArrayNullableTags(): void
    {
        /** @var PgArrayModel $model */
        $model = PgArrayModel::create(['tags' => ['key', null, '', '2']]);

        static::assertInstanceOf(PgArrayModel::class, $model);
        static::assertEquals(['key', '2'], $model->tags);
        static::assertEquals('{key,2}', $model->getRawOriginal('tags'));
    }

    public function testCreateAndFindByTag(): void
    {
        PgArrayModel::create(['tags' => ['key', null, '', '2']]);

        static::assertNotNull(PgArrayModel::byTag('key')->first());
        static::assertNotNull(PgArrayModel::byTag('2')->first());
        static::assertNull(PgArrayModel::byTag('test')->first());
    }

    public function testFindOnlyOneValue(): void
    {
        $model0 = PgArrayModel::create(['tags' => ['2']]);
        $model1 = PgArrayModel::create(['tags' => ['1000', null, '', '2']]);
        $model2 = PgArrayModel::create(['tags' => ['2', '1', '3']]);
        $model3 = PgArrayModel::create(['tags' => ['2']]);
        $model4 = PgArrayModel::create(['tags' => []]);
        $model5 = PgArrayModel::create(['tags' => []]);
        $model6 = PgArrayModel::create(['tags' => []]);
        $model7 = PgArrayModel::create(['tags' => [null]]);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContainsOnly($q, 'tags', '2');
        $result = $q->get();
        //        dd($result->map->toArray());
        static::assertCount(6, $result);
    }


    public function testFindContainValue(): void
    {
        $model0 = PgArrayModel::create(['tags' => ['2']]);
        $model1 = PgArrayModel::create(['tags' => ['1000', null, '', '2']]);
        $model2 = PgArrayModel::create(['tags' => ['2', '1', '3']]);
        $model3 = PgArrayModel::create(['tags' => ['2']]);
        $model4 = PgArrayModel::create(['tags' => []]);
        $model5 = PgArrayModel::create(['tags' => []]);
        $model6 = PgArrayModel::create(['tags' => []]);
        $model7 = PgArrayModel::create(['tags' => [null]]);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', '2');
        $result = $q->get();
        static::assertCount(4, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', ['2']);
        $result = $q->get();
        static::assertCount(4, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', ['2', '3']);
        $result = $q->get();
        static::assertCount(1, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', '3');
        $result = $q->get();
        static::assertCount(1, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', null);
        $result = $q->get();
        static::assertCount(8, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', '');
        $result = $q->get();
        static::assertCount(8, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', 'null');
        $result = $q->get();
        static::assertCount(0, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', static fn() => '{2}');
        $result = $q->get();
        static::assertCount(4, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', static fn() => '{2,4}');
        $result = $q->get();
        static::assertCount(0, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', static fn() => '{2,3}');
        $result = $q->get();
        static::assertCount(1, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContains($q, 'tags', static fn() => '{}');
        $result = $q->get();
        static::assertCount(8, $result);
    }


    public function testFindContainAnyValue(): void
    {
        $model0 = PgArrayModel::create(['tags' => ['2']]);
        $model1 = PgArrayModel::create(['tags' => ['1000', null, '', '2']]);
        $model2 = PgArrayModel::create(['tags' => ['2', '1', '3']]);
        $model3 = PgArrayModel::create(['tags' => ['2']]);
        $model4 = PgArrayModel::create(['tags' => []]);
        $model5 = PgArrayModel::create(['tags' => []]);
        $model6 = PgArrayModel::create(['tags' => []]);
        $model7 = PgArrayModel::create(['tags' => [null]]);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContainsAny($q, 'tags', '2');
        $result = $q->get();
        static::assertCount(4, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContainsAny($q, 'tags', '3');
        $result = $q->get();
        static::assertCount(1, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContainsAny($q, 'tags', null);
        $result = $q->get();
        static::assertCount(0, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContainsAny($q, 'tags', '');
        $result = $q->get();
        static::assertCount(0, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayContainsAny($q, 'tags', 'null');
        $result = $q->get();
        static::assertCount(0, $result);
    }

    public function testFindOverlapArray(): void
    {
        $model0 = PgArrayModel::create(['tags' => ['2']]);
        $model1 = PgArrayModel::create(['tags' => ['1000', null, '', '2']]);
        $model2 = PgArrayModel::create(['tags' => ['2', '1', '3']]);
        $model3 = PgArrayModel::create(['tags' => ['2']]);
        $model4 = PgArrayModel::create(['tags' => []]);
        $model5 = PgArrayModel::create(['tags' => []]);
        $model6 = PgArrayModel::create(['tags' => ['4', '10', '3']]);
        $model7 = PgArrayModel::create(['tags' => [null]]);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayOverlapWith($q, 'tags', ['2', '3']);
        $result = $q->get();
        static::assertCount(5, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayOverlapWith($q, 'tags', []);
        $result = $q->get();
        static::assertCount(0, $result);

        $q = $model1->newModelQuery();
        $model1->scopeWherePgArrayOverlapWith($q, 'tags', ['3']);
        $result = $q->get();
        static::assertCount(2, $result);
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
