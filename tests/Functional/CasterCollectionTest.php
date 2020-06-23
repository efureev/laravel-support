<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Php\Support\Helpers\Json;
use Php\Support\Laravel\Tests\TestClasses\Entity\ArrayCollection;
use Php\Support\Laravel\Tests\TestClasses\Entity\Component;
use Php\Support\Laravel\Tests\TestClasses\Entity\ComponentCollection;
use Php\Support\Laravel\Tests\TestClasses\Models\CasterCollection;

class CasterCollectionTest extends AbstractFunctionalTestCase
{
    public function testCreateWithEmptyCollection(): void
    {
        $model = CasterCollection::create();

        static::assertInstanceOf(CasterCollection::class, $model);
        static::assertNull($model->components);
        static::assertNull($model->arrays);
        static::assertNull($model->getOriginal('components'));
        static::assertNull($model->getOriginal('arrays'));
        static::assertNull($model->getRawOriginal('components'));
        static::assertNull($model->getRawOriginal('arrays'));
    }

    public function testCreateWithEmptyCollection2(): void
    {
        $model = CasterCollection::create(['components' => null, 'arrays' => null]);

        static::assertInstanceOf(CasterCollection::class, $model);
        static::assertInstanceOf(ComponentCollection::class, $model->components);
        static::assertInstanceOf(ComponentCollection::class, $model->getOriginal('components'));
        static::assertInstanceOf(ArrayCollection::class, $model->arrays);
        static::assertInstanceOf(ArrayCollection::class, $model->getOriginal('arrays'));

        static::assertEquals('[]', $model->getRawOriginal('components'));
        static::assertEquals('[]', $model->getRawOriginal('arrays'));
    }

    private static function createComponent($isMain = false): array
    {
        return [
            'module' => 'news',
            'isMain' => $isMain,
            'params' => [],
        ];
    }


    public function testCreateArrayCollection(): void
    {
        $data  = [
            self::createComponent(),
            self::createComponent(true),
            self::createComponent(),
        ];
        $model = CasterCollection::create(['arrays' => $data]);

        static::assertInstanceOf(ArrayCollection::class, $model->arrays);
        static::assertInstanceOf(ArrayCollection::class, $model->getOriginal('arrays'));

        static::assertCount(3, $model->arrays);
        static::assertEquals(
            '[{"module":"news","isMain":false,"params":[]},{"module":"news","isMain":true,"params":[]},{"module":"news","isMain":false,"params":[]}]',
            $model->getRawOriginal('arrays')
        );

        static::assertJsonStringEqualsJsonString(Json::encode($data), $model->getRawOriginal('arrays'));
    }

    public function testCreateCollection(): void
    {
        $data  = [
            self::createComponent(),
            self::createComponent(true),
            self::createComponent(),
            self::createComponent(),
        ];
        $model = CasterCollection::create(['components' => $data]);

        static::assertInstanceOf(ComponentCollection::class, $model->components);
        static::assertInstanceOf(ComponentCollection::class, $model->getOriginal('components'));

        static::assertCount(4, $model->components);

        foreach ($model->components as $k => $component) {
            static::assertInstanceOf(Component::class, $component);
            static::assertEquals($data[$k]['module'], $component->module);
        }

        static::assertJsonStringEqualsJsonString(Json::encode($data), $model->getRawOriginal('components'));
        static::assertJsonStringEqualsJsonString(
            '[{"name":"news"},{"name":"news"},{"name":"news"},{"name":"news"}]',
            $model->components->toJson()
        );
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
