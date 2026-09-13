<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Pagination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Php\Support\Laravel\Pagination\PaginatedResourceArray;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\Database\Seeders\BaseTableSeeder;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use PHPUnit\Framework\Attributes\Test;

class PaginatedResourceArrayTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(self::migrationsPath('2020_08_12_075141_create_base_table.php'));
        $this->seed(BaseTableSeeder::class);

        Paginator::currentPathResolver(static fn(): string => 'http://localhost/items');
    }

    private function collection(int $perPage = 2): ResourceCollection
    {
        return new BaseCollection(BaseModel::query()->paginate($perPage));
    }

    #[Test]
    public function it_returns_data_links_and_meta(): void
    {
        $array = (new PaginatedResourceArray($this->collection()))->toArray(Request::create('/items'));

        self::assertArrayHasKey('data', $array);
        self::assertArrayHasKey('links', $array);
        self::assertArrayHasKey('meta', $array);

        self::assertCount(2, $array['data']);
        self::assertSame(['first', 'last', 'prev', 'next'], array_keys($array['links']));
        self::assertSame(3, $array['meta']['total']);
        self::assertSame(2, $array['meta']['per_page']);
    }

    #[Test]
    public function meta_excludes_the_keys_promoted_into_links(): void
    {
        $array = (new PaginatedResourceArray($this->collection()))->toArray(Request::create('/items'));

        foreach (['data', 'first_page_url', 'last_page_url', 'prev_page_url', 'next_page_url'] as $key) {
            self::assertArrayNotHasKey($key, $array['meta']);
        }
    }

    /**
     * The whole point of extending {@see PaginatedResourceResponse} is that the output stays
     * identical to what the framework would have produced. Rebuild the landmark from the same
     * input instead of hard-coding a snapshot: if Laravel ever changes the shape, this fails
     * loudly rather than drifting silently — which is exactly how the old hand-written copy
     * fell behind.
     */
    #[Test]
    public function the_output_matches_what_the_framework_would_have_encoded(): void
    {
        $request = Request::create('/items');

        $expected = (new PaginatedResourceResponse($this->collection()))
            ->toResponse($request)
            ->getData(true);

        $actual = (new PaginatedResourceArray($this->collection()))->toArray($request);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function it_honours_the_resources_pagination_information_hook(): void
    {
        // The hand-written copy ignored this hook entirely.
        $array = (new PaginatedResourceArray(new HookedCollection(BaseModel::query()->paginate(2))))
            ->toArray(Request::create('/items'));

        self::assertSame('hooked', $array['meta']['custom']);
    }

    #[Test]
    public function it_honours_a_custom_wrapper_name(): void
    {
        $array = (new PaginatedResourceArray(new WrappedCollection(BaseModel::query()->paginate(2))))
            ->toArray(Request::create('/items'));

        self::assertArrayHasKey('items', $array);
        self::assertArrayNotHasKey('data', $array);
        self::assertCount(2, $array['items']);
    }

    #[Test]
    public function it_still_wraps_when_the_resource_declares_no_wrapper(): void
    {
        // links/meta are "with" data, which forces a default 'data' wrapper even when
        // $wrap is null — same rule the framework applies.
        $array = (new PaginatedResourceArray(new UnwrappedCollection(BaseModel::query()->paginate(2))))
            ->toArray(Request::create('/items'));

        self::assertArrayHasKey('data', $array);
        self::assertCount(2, $array['data']);
    }

    #[Test]
    public function it_merges_additional_data(): void
    {
        $collection = $this->collection()->additional(['status' => 'ok']);

        $array = (new PaginatedResourceArray($collection))->toArray(Request::create('/items'));

        self::assertSame('ok', $array['status']);
    }
}

class BaseCollection extends ResourceCollection
{
    public $collects = JsonResource::class;
}

class HookedCollection extends BaseCollection
{
    /**
     * @param array<string, mixed> $paginated
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        $default['meta']['custom'] = 'hooked';

        return $default;
    }
}

class UnwrappedCollection extends BaseCollection
{
    public static $wrap = null;
}

class WrappedCollection extends BaseCollection
{
    public static $wrap = 'items';
}
