<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Models;

use Illuminate\Database\Eloquent\Model;
use Php\Support\Exceptions\MethodNotAllowedException;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Traits\Models\AllowToExecute;
use PHPUnit\Framework\Attributes\Test;

class AllowToExecuteTest extends AbstractTestCase
{
    #[Test]
    public function callBase()
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage(
            'Method Not Allowed: You should call this method like this: Remover::exec($model)'
        );

        $class->delete();
    }

    #[Test]
    public function callWithDisablePossibility()
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('Method Not Allowed: You should call this method like this: new Query($model)');

        $class->newQuery();
    }

    #[Test]
    public function callWithPossibility()
    {
        $class = $this->buildClass();
        $class->addMethodToAllowList('newQuery');

        $class->newQuery();

        static::assertNotNull($class);
    }

    #[Test]
    public function callFn()
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage("QQ");
        $class->handle(1, 2);
    }

    private function buildClass(): Model
    {
        return new class extends Model {
            use AllowToExecute;

            protected static function booting(): void
            {
                static::addMethodToDisallowMap('delete', 'Remover::exec($model)');
                static::addMethodToDisallowMap('newQuery', 'new Query($model)');
                static::addMethodToDisallowMap(
                    'handle',
                    static fn() => throw new MethodNotAllowedException("QQ")
                );
            }

            public function delete()
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__);
            }

            public function handle($value, $value2)
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__, $value, $value2);
            }

            public function newQuery()
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__);
            }
        };
    }
}
