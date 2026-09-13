<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Php\Support\Laravel\Exceptions\InvalidParamException;
use Php\Support\Laravel\Repositories\AbstractRepository;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\Database\Seeders\BaseTableSeeder;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use PHPUnit\Framework\Attributes\Test;

class AbstractRepositoryTest extends AbstractTestCase
{
    private BaseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(self::migrationsPath('2020_08_12_075141_create_base_table.php'));
        $this->seed(BaseTableSeeder::class);

        $this->repository = new BaseRepository();
    }

    #[Test]
    public function it_finds_a_model_by_an_integer_key(): void
    {
        // `base_table.id` is an auto-increment int — the repository must accept int keys,
        // not just strings.
        $model = $this->repository->findModel(1);

        self::assertInstanceOf(BaseModel::class, $model);
        self::assertSame(1, $model->getKey());
    }

    #[Test]
    public function it_finds_a_model_by_a_string_key(): void
    {
        $model = $this->repository->findModel('1');

        self::assertInstanceOf(BaseModel::class, $model);
        self::assertSame(1, $model->getKey());
    }

    #[Test]
    public function it_passes_an_already_loaded_model_straight_through(): void
    {
        $existing = BaseModel::query()->firstOrFail();

        self::assertSame($existing, $this->repository->findModel($existing));
    }

    #[Test]
    public function it_rejects_a_key_that_is_neither_scalar_nor_a_model(): void
    {
        $this->expectException(InvalidParamException::class);
        $this->expectExceptionMessage('$id must be int, string or ' . Model::class);

        $this->repository->findModel([1, 2]);
    }

    #[Test]
    public function a_missing_model_reports_the_id_that_was_looked_up(): void
    {
        try {
            $this->repository->findModel(9999);
            self::fail('Expected ModelNotFoundException.');
        } catch (ModelNotFoundException $e) {
            self::assertSame(BaseModel::class, $e->getModel());
            self::assertSame([9999], $e->getIds(), 'The looked-up id must survive into the exception.');
            self::assertStringContainsString('9999', $e->getMessage());
        }
    }

    #[Test]
    public function it_can_return_null_instead_of_throwing(): void
    {
        self::assertNull($this->repository->findModel(9999, throw: false));
    }

    #[Test]
    public function it_lists_all_models(): void
    {
        $all = $this->repository->all();

        self::assertInstanceOf(Collection::class, $all);
        self::assertCount(3, $all);
    }

    #[Test]
    public function it_creates_a_model_when_no_target_is_given(): void
    {
        $model = $this->repository->store(['name' => 'created']);

        self::assertTrue($model->exists);
        self::assertSame('created', $model->getAttribute('name'));
        self::assertCount(4, $this->repository->all());
    }

    #[Test]
    public function it_updates_the_model_identified_by_the_given_key(): void
    {
        $model = $this->repository->store(['name' => 'renamed'], 1);

        self::assertSame(1, $model->getKey());
        self::assertSame('renamed', $model->fresh()?->getAttribute('name'));
        self::assertCount(3, $this->repository->all());
    }

    #[Test]
    public function it_deletes_the_model_identified_by_the_given_key(): void
    {
        $this->repository->delete(1);

        self::assertNull(BaseModel::query()->find(1));
        self::assertCount(2, $this->repository->all());
    }

    #[Test]
    public function deleting_a_missing_model_throws(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(9999);
    }

    #[Test]
    public function it_registers_the_requested_eager_loads(): void
    {
        $builder = $this->repository->with(['someRelation']);

        self::assertInstanceOf(Builder::class, $builder);
        self::assertArrayHasKey('someRelation', $builder->getEagerLoads());
    }
}

final class BaseRepository extends AbstractRepository
{
    protected string $modelClass = BaseModel::class;
}
