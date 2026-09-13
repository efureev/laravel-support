<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Models;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\Database\Seeders\BaseTableSeeder;
use Php\Support\Laravel\Traits\Models\Cachers\CacherContract;
use Php\Support\Laravel\Traits\Models\Cachers\DummyCacher;
use Php\Support\Laravel\Traits\Models\HasModelEntityCache;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class HasModelEntityCacheTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(self::migrationsPath('2020_08_12_075141_create_base_table.php'));
        $this->seed(BaseTableSeeder::class);

        CachedModel::enableCache();
        CachedModel::flushResolvedCacher();
        Config::set('cache.resolver.class', null);
        Cache::store()->flush();
        SpyCacher::$forgotten            = [];
        SpyCacher::$collectionsForgotten = 0;
    }

    protected function tearDown(): void
    {
        Config::set('cache.resolver.class', null);
        CachedModel::flushResolvedCacher();
        CachedModel::enableCache();

        parent::tearDown();
    }

    #[Test]
    public function a_custom_resolver_is_instantiated_not_called_as_a_function(): void
    {
        // `$cls(...)` on a class-name string is a FUNCTION call, so the old code raised
        // "Call to undefined function" the moment anyone configured a resolver.
        Config::set('cache.resolver.class', SpyCacher::class);

        CachedModel::cacheForgetByKey('7');

        self::assertSame(['app:models:CachedModel:7'], SpyCacher::$forgotten);
    }

    #[Test]
    public function a_resolver_that_is_not_a_cacher_is_rejected(): void
    {
        Config::set('cache.resolver.class', \stdClass::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(CacherContract::class);

        CachedModel::cacheForgetByKey('7');
    }

    #[Test]
    public function a_missing_or_unknown_resolver_falls_back_to_the_store_based_choice(): void
    {
        Config::set('cache.resolver.class', 'Acme\\NoSuchCacher');

        self::assertInstanceOf(DummyCacher::class, CachedModel::cacher());
    }

    #[Test]
    public function saving_and_deleting_invalidate_the_entity_and_the_collection(): void
    {
        Config::set('cache.resolver.class', SpyCacher::class);

        $model = CachedModel::query()->firstOrFail();
        $model->setAttribute('name', 'renamed');
        $model->save();

        self::assertSame(["app:models:CachedModel:{$model->getKey()}"], SpyCacher::$forgotten);
        self::assertSame(1, SpyCacher::$collectionsForgotten);

        $model->delete();

        self::assertCount(2, SpyCacher::$forgotten);
        self::assertSame(2, SpyCacher::$collectionsForgotten);
    }

    #[Test]
    public function remember_caches_the_callback_result(): void
    {
        $calls = 0;
        $fn    = static function () use (&$calls): string {
            $calls++;

            return 'value';
        };

        self::assertSame('value', CachedModel::remember($fn, 'k'));
        self::assertSame('value', CachedModel::remember($fn, 'k'));
        self::assertSame(1, $calls, 'The second call must be served from the cache.');
    }

    #[Test]
    public function forget_drops_what_remember_stored(): void
    {
        $calls = 0;
        $fn    = static function () use (&$calls): string {
            $calls++;

            return 'value';
        };

        CachedModel::remember($fn, 'k');
        self::assertTrue(CachedModel::forget('k'));
        CachedModel::remember($fn, 'k');

        self::assertSame(2, $calls, 'forget() must force the callback to run again.');
    }

    #[Test]
    public function disabling_the_cache_bypasses_the_store(): void
    {
        CachedModel::disableCache();

        $calls = 0;
        $fn    = static function () use (&$calls): string {
            $calls++;

            return 'value';
        };

        CachedModel::remember($fn, 'k');
        CachedModel::remember($fn, 'k');

        self::assertSame(2, $calls, 'Nothing may be cached while caching is disabled.');
    }

    #[Test]
    public function collection_invalidation_respects_the_disabled_flag_like_entity_invalidation_does(): void
    {
        Config::set('cache.resolver.class', SpyCacher::class);
        CachedModel::cacher(); // resolve while still enabled

        CachedModel::disableCache();
        CachedModel::forgetCollection();

        self::assertSame(0, SpyCacher::$collectionsForgotten);
    }

    #[Test]
    public function enable_cache_reverses_disable_cache(): void
    {
        CachedModel::disableCache();
        self::assertFalse(CachedModel::$cacheEnable);

        CachedModel::enableCache();
        self::assertTrue(CachedModel::$cacheEnable);
    }

    #[Test]
    public function without_cache_restores_the_previous_state_even_on_throw(): void
    {
        self::assertSame('done', CachedModel::withoutCache(static fn(): string => 'done'));
        self::assertTrue(CachedModel::$cacheEnable);

        try {
            CachedModel::withoutCache(static fn() => throw new RuntimeException('boom'));
        } catch (RuntimeException) {
            // expected
        }

        self::assertTrue(CachedModel::$cacheEnable, 'The flag must be restored after a throw.');
    }

    #[Test]
    public function an_array_store_gets_the_dummy_cacher(): void
    {
        self::assertInstanceOf(DummyCacher::class, CachedModel::cacher());
    }
}

class CachedModel extends Model
{
    use HasModelEntityCache;

    public $timestamps = false;

    protected $table = 'base_table';

    protected $fillable = ['name'];

    /** Test seam: the resolved cacher is protected on the trait. */
    public static function cacher(): CacherContract
    {
        return static::resolveStoreDriver();
    }

    /** Test seam: collection invalidation is protected on the trait. */
    public static function forgetCollection(?string $key = null): bool
    {
        return static::cacheForgetCollection($key);
    }
}

class SpyCacher implements CacherContract
{
    /** @var string[] */
    public static array $forgotten = [];

    public static int $collectionsForgotten = 0;

    public function __construct(private readonly string $model, protected readonly ?Store $store = null)
    {
    }

    public function prefixKey(?string $key = null, ?string $prefix = null): string
    {
        $prefix ??= class_basename($this->model);

        return "app:models:$prefix:$key";
    }

    public function cacheForgetCollection(?string $key = null): bool
    {
        self::$collectionsForgotten++;

        return true;
    }

    public function forgetByKey(string $key): bool
    {
        self::$forgotten[] = $this->prefixKey($key);

        return true;
    }
}
