<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Php\Support\Exceptions\UnknownMethodException;
use Php\Support\Laravel\Tests\Database\Seeders\BaseTableSeeder;
use Php\Support\Laravel\Tests\TestClasses\HasCustomModel;
use Php\Support\Laravel\Tests\TestClasses\HasModel;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;

class ModelableTest extends AbstractFunctionalTestCase
{
    public function testBase(): void
    {
        $instance = new HasModel();

        static::assertEquals(BaseModel::class, $instance::modelClass());
        static::assertInstanceOf(BaseModel::class, $instance::getModelInstance());
        static::assertInstanceOf(BaseModel::class, $instance->model());

        //        static::assertInstanceOf(BaseModel::class, $instance->modelKeyValue());
        static::assertInstanceOf(Builder::class, $instance->newQueryWithoutScopes());

        $model1 = $instance->findModelQuery(1);
        static::assertInstanceOf(BaseModel::class, $model1->first());

        $model2 = $instance->findModelOrFail(2);
        static::assertInstanceOf(BaseModel::class, $model2);
        //        static::assertEquals('id', $instance->modelKeyName());

        $model2 = $instance->findModelOrFail([1, 2, 3]);
        static::assertInstanceOf(BaseModel::class, $model2);
    }

    public function testBaseCollection(): void
    {
        $instance = new HasModel();

        $q = $instance->findModelQuery([1, 2, 3]);
        static::assertInstanceOf(Collection::class, $q->get());
        static::assertCount(3, $q->get());


        $model = $instance->findModelOrFail([1, 2, 3]);
        static::assertInstanceOf(BaseModel::class, $model);
        static::assertEquals(1, $model->getKey());
    }


    public function testBase2(): void
    {
        $instance = new HasCustomModel();

        $model1 = $instance->findModelQuery();
        static::assertInstanceOf(BaseModel::class, $model1->first());

        $model2 = $instance->findModelOrFail();
        static::assertInstanceOf(BaseModel::class, $model2);
    }

    public function testModelNotFound(): void
    {
        $instance = new HasModel();
        $this->expectException(ModelNotFoundException::class);
        $instance->findModelOrFail(4);
    }


    public function testMethodIsMissed(): void
    {
        $instance = new HasModel();
        $this->expectException(UnknownMethodException::class);
        $q = $instance->findModelQuery();
        static::assertInstanceOf(Builder::class, $q);
        static::assertInstanceOf(HasModel::class, $q->getModel());
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_08_12_075141_create_base_table.php');

        $this->seed(BaseTableSeeder::class);
    }
}
