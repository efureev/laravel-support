<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Requests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\Database\Seeders\BaseTableSeeder;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use Php\Support\Laravel\Traits\Requests\RequestModelable;
use PHPUnit\Framework\Attributes\Test;

class RequestModelableTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(self::migrationsPath('2020_08_12_075141_create_base_table.php'));
        $this->seed(BaseTableSeeder::class);
    }

    #[Test]
    public function it_resolves_the_model_from_the_request_input(): void
    {
        $request = BaseModelRequest::create('/', 'GET', ['id' => 2]);

        $model = $request->findModelOrFail();

        self::assertInstanceOf(BaseModel::class, $model);
        self::assertSame(2, $model->getKey());
    }

    #[Test]
    public function it_builds_a_query_scoped_to_the_requested_key(): void
    {
        $request = BaseModelRequest::create('/', 'GET', ['id' => 1]);

        $query = $request->findModelQuery();

        self::assertInstanceOf(Builder::class, $query);
        self::assertSame(1, $query->firstOrFail()->getKey());
    }

    #[Test]
    public function it_accepts_a_set_of_keys(): void
    {
        $request = BaseModelRequest::create('/', 'GET', ['id' => [1, 3]]);

        self::assertCount(2, $request->findModelQuery()->get());
    }

    #[Test]
    public function an_explicit_key_wins_over_the_request_input(): void
    {
        $request = BaseModelRequest::create('/', 'GET', ['id' => 1]);

        self::assertSame(3, $request->findModelOrFail(3)->getKey());
    }

    #[Test]
    public function a_missing_model_throws(): void
    {
        $request = BaseModelRequest::create('/', 'GET', ['id' => 999]);

        $this->expectException(ModelNotFoundException::class);

        $request->findModelOrFail();
    }

    #[Test]
    public function the_query_uses_the_qualified_key_but_the_request_uses_the_plain_one(): void
    {
        // modelKeyName() feeds a WHERE clause and must stay table-qualified; the gainer reads
        // request input, where a dot means nested access, so it gets the plain name.
        self::assertSame('base_table.id', BaseModelRequest::exposedKeyName());
        self::assertSame('id', BaseModelRequest::exposedInputKeyName());
    }
}

class BaseModelRequest extends Request
{
    use RequestModelable;

    public static function exposedKeyName(): string
    {
        return static::modelKeyName();
    }

    public static function exposedInputKeyName(): string
    {
        return static::modelInputKeyName();
    }

    /**
     * @return class-string<BaseModel>
     */
    protected static function modelClass(): string
    {
        return BaseModel::class;
    }
}
