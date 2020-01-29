<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Illuminate\Database\Eloquent\Collection;
use Php\Support\Laravel\Tests\Models\Params;
use Php\Support\Laravel\Tests\Models\TestModel;

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

            static::assertIsString($item->getOriginal('params'));
            static::assertEquals('[]', $item->getOriginal('params'));

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
                'config'    => [
                    'key' => 2,
                ],
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
            static::assertEquals('{"key":2}', $item->getOriginal('config'));
            static::assertIsString($item->getOriginal('params'));
            static::assertEquals('{"key":1,"config":{"test":"value"},"testParam":"val"}', $item->getOriginal('params'));

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
                    'key' => [
                        'key' => 2,
                    ],
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
            static::assertEquals('{"key":{"key":2}}', $item->getOriginal('config'));
            static::assertIsString($item->getOriginal('params'));
            static::assertEquals('{"key":1,"config":{"test":"value"},"testParam":"val"}', $item->getOriginal('params'));

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