<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Models\Cachers;

use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Php\Support\Laravel\Traits\Models\Cachers\RedisCacher;
use PHPUnit\Framework\Attributes\Test;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use Throwable;

/**
 * Functional cover for {@see RedisCacher} against a real Redis.
 *
 * Skipped when no server is reachable; CI and `composer test:docker` both provide one.
 */
class RedisCacherTest extends AbstractTestCase
{
    private RedisStore $store;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('cache.default', 'redis');
        $app['config']->set('cache.prefix', 'lst_');
        $app['config']->set('database.redis.client', self::envValue('REDIS_CLIENT', 'phpredis'));
        $app['config']->set(
            'database.redis.default',
            [
                'host'     => self::envValue('REDIS_HOST', '127.0.0.1'),
                'port'     => (int)self::envValue('REDIS_PORT', '6379'),
                'database' => (int)self::envValue('REDIS_DB', '1'),
            ]
        );
        $app['config']->set('database.redis.cache', $app['config']->get('database.redis.default'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('redis') && !class_exists(\Predis\Client::class)) {
            self::markTestSkipped('Neither ext-redis nor predis/predis is available.');
        }

        $store = Cache::store('redis')->getStore();

        if (!$store instanceof RedisStore) {
            self::markTestSkipped('The redis cache store is not configured.');
        }

        try {
            $store->connection()->client()->ping();
        } catch (Throwable $e) {
            self::markTestSkipped('Redis is not reachable: ' . $e->getMessage());
        }

        $this->store = $store;
        $this->store->flush();
    }

    protected function tearDown(): void
    {
        if (isset($this->store)) {
            $this->store->flush();
        }

        parent::tearDown();
    }

    private function cacher(): RedisCacher
    {
        return new RedisCacher(BaseModel::class, $this->store);
    }

    #[Test]
    public function it_prefixes_keys_with_the_model_basename(): void
    {
        self::assertSame('app:models:BaseModel:7', $this->cacher()->prefixKey('7'));
        self::assertSame('app:models:Custom:7', $this->cacher()->prefixKey('7', 'Custom'));
    }

    #[Test]
    public function it_forgets_a_single_entity(): void
    {
        $this->store->put('app:models:BaseModel:7', 'value', 60);

        self::assertTrue($this->cacher()->forgetByKey('7'));
        self::assertNull($this->store->get('app:models:BaseModel:7'));
    }

    #[Test]
    public function it_deletes_every_key_matching_the_collection_pattern(): void
    {
        // The original Lua bound `local keys = unpack(redis.call('keys', ...))`, which takes
        // only the FIRST element — so exactly one key was deleted while the method still
        // reported success. Three keys is the smallest set that catches it.
        foreach (['list:page:1', 'list:page:2', 'list:page:3'] as $suffix) {
            $this->store->put("app:models:BaseModel:$suffix", 'value', 60);
        }
        $this->store->put('app:models:BaseModel:42', 'entity', 60);

        self::assertTrue($this->cacher()->cacheForgetCollection());

        foreach (['list:page:1', 'list:page:2', 'list:page:3'] as $suffix) {
            self::assertNull(
                $this->store->get("app:models:BaseModel:$suffix"),
                "Key $suffix survived the pattern delete."
            );
        }

        self::assertSame('entity', $this->store->get('app:models:BaseModel:42'), 'Only list:* may go.');
    }

    #[Test]
    public function it_reports_false_when_nothing_matched(): void
    {
        self::assertFalse($this->cacher()->cacheForgetCollection());
    }

    #[Test]
    public function it_deletes_every_key_matching_a_custom_pattern(): void
    {
        foreach (['tags:a', 'tags:b'] as $suffix) {
            $this->store->put("app:models:BaseModel:$suffix", 'value', 60);
        }

        self::assertTrue($this->cacher()->cacheForgetCollection('tags:*'));

        self::assertNull($this->store->get('app:models:BaseModel:tags:a'));
        self::assertNull($this->store->get('app:models:BaseModel:tags:b'));
    }
}
