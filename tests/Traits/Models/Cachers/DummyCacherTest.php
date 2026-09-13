<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Models\Cachers;

use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use Php\Support\Laravel\Traits\Models\Cachers\DummyCacher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DummyCacherTest extends TestCase
{
    private function cacher(): DummyCacher
    {
        return new DummyCacher(BaseModel::class);
    }

    #[Test]
    public function it_prefixes_keys_with_the_model_basename(): void
    {
        self::assertSame('app:models:BaseModel:7', $this->cacher()->prefixKey('7'));
        self::assertSame('app:models:BaseModel:', $this->cacher()->prefixKey());
    }

    #[Test]
    public function the_prefix_can_be_overridden(): void
    {
        self::assertSame('app:models:Other:7', $this->cacher()->prefixKey('7', 'Other'));
    }

    #[Test]
    public function invalidation_is_a_no_op_that_reports_success(): void
    {
        // A store that cannot delete by pattern must not pretend to fail — callers treat
        // false as "nothing was dropped", which would be misleading here.
        self::assertTrue($this->cacher()->cacheForgetCollection());
        self::assertTrue($this->cacher()->cacheForgetCollection('list:*'));
        self::assertTrue($this->cacher()->forgetByKey('7'));
    }

    #[Test]
    public function it_accepts_the_same_constructor_shape_as_the_other_cachers(): void
    {
        self::assertInstanceOf(DummyCacher::class, new DummyCacher(BaseModel::class, null));
    }
}
