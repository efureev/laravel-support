<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Illuminate\Database\Eloquent\Collection;
use Php\Support\Laravel\Tests\TestClasses\Entity\EmptyParams;
use Php\Support\Laravel\Tests\TestClasses\Entity\Params;
use Php\Support\Laravel\Tests\TestClasses\Models\PgArrayModel;
use Php\Support\Laravel\Tests\TestClasses\Models\TestDirtyModel;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;

class CasterTraitTest extends AbstractFunctionalTestCase
{
    public function testCreateAndGetWithNullParams(): void
    {
        /** @var Collection $list */
        $list = factory(TestModel::class, 5)->create();

        static::assertCount(5, $list);

        /** @var TestModel $item */
        foreach ($list as $item) {
            static::assertInstanceOf(TestModel::class, $item);
            static::assertNotEmpty($item->title);
            static::assertIsString($item->title);

            static::assertIsString($item->str);
            static::assertNotEmpty($item->str);
            static::assertNull($item->str_empty);
            static::assertNull($item->int);

            static::assertIsBool($item->enabled);
            static::assertNull($item->config);
            static::assertEmpty($item->config);
            static::assertNull($item->params);
            static::assertNull($item->getOriginal('params'));
        }
    }

    public function testCreateAndGetWithEmptyArrayParams(): void
    {
        /** @var Collection $list */
        $list = factory(TestModel::class, 5)->create(
            [
                'params'    => [],
                'config'    => [],
                'str_empty' => '',
                'int'       => 0,
            ]
        );

        static::assertCount(5, $list);

        /** @var TestModel $item */
        foreach ($list as $item) {
            static::assertInstanceOf(TestModel::class, $item);
            static::assertNotEmpty($item->title);
            static::assertIsString($item->title);
            static::assertIsBool($item->enabled);
            static::assertIsArray($item->config);
            static::assertEquals([], $item->config);

            static::assertIsString($item->str);
            static::assertNotEmpty($item->str);
            static::assertIsString($item->str_empty);
            static::assertEmpty($item->str_empty);
            static::assertIsInt($item->int);
            static::assertEquals(0, $item->int);

            static::assertInstanceOf(Params::class, $item->getOriginal('params'));

            static::assertIsString($item->getRawOriginal('params'));

            static::assertEquals('[]', $item->getRawOriginal('params'));

            static::assertInstanceOf(Params::class, $item->params);
            static::assertEquals(
                [
                    'key'       => null,
                    'config'    => [],
                    'testParam' => null,
                ],
                $item->params->toArray()
            );
        }
    }

    public function testCreateAndGetWithFillArrayParams(): void
    {
        /** @var Collection $list */
        $list = factory(TestModel::class, 5)->create(
            [
                'str'       => 'string',
                'str_empty' => 'string empty',
                'int'       => 7,
                'config'    => ['key' => 2],
                'params'    => [
                    'key'       => 1,
                    'config'    => ['test' => 'value'],
                    'testParam' => 'val',
                ],
            ]
        );

        static::assertCount(5, $list);

        /** @var TestModel $item */
        foreach ($list as $item) {
            static::assertInstanceOf(TestModel::class, $item);
            static::assertNotEmpty($item->title);
            static::assertIsString($item->title);
            static::assertIsBool($item->enabled);

            static::assertIsString($item->str);
            static::assertEquals('string', $item->str);
            static::assertIsString($item->str_empty);
            static::assertEquals('string empty', $item->str_empty);
            static::assertIsInt($item->int);
            static::assertEquals(7, $item->int);

            static::assertEquals(['key' => 2], $item->config);
            static::assertEquals('{"key":2}', $item->getRawOriginal('config'));
            static::assertIsString($item->getRawOriginal('params'));
            static::assertEquals(
                '{"key":1,"config":{"test":"value"},"testParam":"val"}',
                $item->getRawOriginal('params')
            );

            static::assertInstanceOf(Params::class, $item->params);
            static::assertEquals(
                [
                    'key'       => 1,
                    'config'    => ['test' => 'value'],
                    'testParam' => 'val',
                ],
                $item->params->toArray()
            );
        }
    }

    public function testCreateAndGetWithClassParams(): void
    {
        /** @var Collection $list */
        $list = factory(TestModel::class, 5)->create(
            [
                'str_empty' => null,
                'config'    => [
                    'key' => ['key' => 2],
                ],
                'params'    => new Params(
                    [
                        'key'       => 1,
                        'config'    => ['test' => 'value'],
                        'testParam' => 'val',
                    ]
                ),
            ]
        );

        static::assertCount(5, $list);

        /** @var TestModel $item */
        foreach ($list as $item) {
            static::assertInstanceOf(TestModel::class, $item);
            static::assertNotEmpty($item->title);
            static::assertIsString($item->title);
            static::assertIsBool($item->enabled);

            static::assertIsString($item->str);
            static::assertNotEmpty($item->str);
            static::assertNull($item->str_empty);

            static::assertEquals(['key' => ['key' => 2]], $item->config);
            static::assertEquals('{"key":{"key":2}}', $item->getRawOriginal('config'));
            static::assertIsString($item->getRawOriginal('params'));
            static::assertEquals(
                '{"key":1,"config":{"test":"value"},"testParam":"val"}',
                $item->getRawOriginal('params')
            );

            static::assertInstanceOf(Params::class, $item->params);
            static::assertEquals(
                [
                    'key'       => 1,
                    'config'    => ['test' => 'value'],
                    'testParam' => 'val',
                ],
                $item->params->toArray()
            );
        }
    }


    public function testCreateAndCheckDirty(): void
    {
        $model = TestDirtyModel::make(
            [
                'params' => [
                    'key' => 1,
                ],
            ]
        );

        static::assertTrue($model->isDirty('params'));
        static::assertEquals('{"key":1}', $model->getDirty()['params']);

        $model->save();

        $model->fill(
            [
                'params' => [
                    'key' => 1,
                ],
            ]
        );

        static::assertFalse($model->isDirty('params'));
        static::assertEmpty($model->getDirty());


        $model->fill(
            [
                'params' => [
                    'key' => 2,
                ],
            ]
        );

        static::assertTrue($model->isDirty('params'));
        static::assertEquals('{"key":2}', $model->getDirty()['params']);

        $model->save();

        static::assertEquals('{"key":2}', $model->getRawOriginal('params'));
    }


    public function testCreateAndCheckDirtyPgArray(): void
    {
        $model = PgArrayModel::make(['tags' => ['key', 'tag1']]);

        static::assertTrue($model->isDirty('tags'));
        static::assertEquals('{key,tag1}', $model->getDirty()['tags']);

        $model->save();

        $model->fill(['tags' => ['key', 'tag1']]);

        static::assertFalse($model->isDirty('tags'));
        static::assertEmpty($model->getDirty());


        $model->fill(['tags' => ['key', 'tag2']]);

        static::assertTrue($model->isDirty('tags'));
        static::assertEquals('{key,tag2}', $model->getDirty()['tags']);

        $model->save();

        static::assertEquals('{key,tag2}', $model->getRawOriginal('tags'));
    }

    public function testCreateEmptyParams(): void
    {
        $params = new Params();

        static::assertInstanceOf(Params::class, $params);
        static::assertEquals(
            [
                'key'       => null,
                'config'    => [],
                'testParam' => null,
            ],
            $params->toArray()
        );
    }

    public function testCreateFullEmptyParams(): void
    {
        $params = new EmptyParams();

        static::assertInstanceOf(EmptyParams::class, $params);
        static::assertEquals([], $params->toArray());
        static::assertEquals('{}', $params->toJson());
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->withFactories(__DIR__ . '/../database/factories');
    }
}
