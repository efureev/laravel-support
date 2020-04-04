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
        static::assertEquals('{}', $model->getOriginal('tags'));
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
        static::assertEquals('{key,key2,2}', $model->getOriginal('tags'));
    }

    public function testCreateAndGetWithFillArrayNullableTags(): void
    {
        /** @var PgArrayModel $model */
        $model = PgArrayModel::create(['tags' => ['key', null, '', '2']]);

        static::assertInstanceOf(PgArrayModel::class, $model);
        static::assertEquals(['key', '2'], $model->tags);
        static::assertEquals('{key,2}', $model->getOriginal('tags'));
    }

    public function testCreateAndFindByTag(): void
    {
        PgArrayModel::create(['tags' => ['key', null, '', '2']]);

        static::assertNotNull(PgArrayModel::byTag('key')->first());
        static::assertNotNull(PgArrayModel::byTag('2')->first());
        static::assertNull(PgArrayModel::byTag('test')->first());
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
