<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Models;

use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Exceptions\MethodNotAllowedException;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Traits\Models\AllowToExecute;
use PHPUnit\Framework\Attributes\Test;

class AllowToExecuteTest extends AbstractTestCase
{
    #[Test]
    public function callBase(): void
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage(
            'Method Not Allowed: You should call this method like this: Remover::exec($model)'
        );

        $class->delete();
    }

    #[Test]
    public function callWithDisablePossibility(): void
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('Method Not Allowed: You should call this method like this: new Query($model)');

        $class->newQuery();
    }

    #[Test]
    public function callWithPossibility(): void
    {
        $class = $this->buildClass();
        $class->addMethodToAllowList('newQuery');

        $class->newQuery();

        static::assertTrue($class->isAllowToExecute('newQuery'));
    }

    #[Test]
    public function callFn(): void
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage("QQ");
        $class->handle(1, 2);
    }

    #[Test]
    public function removeMethodFromAllowList_undoes_addMethodToAllowList(): void
    {
        $class = $this->buildClass();

        $class->addMethodToAllowList('newQuery');
        static::assertTrue($class->isAllowToExecute('newQuery'));

        $class->removeMethodFromAllowList('newQuery');
        static::assertFalse($class->isAllowToExecute('newQuery'));

        $this->expectException(MethodNotAllowedException::class);
        $class->newQuery();
    }

    #[Test]
    public function the_allow_list_is_per_instance(): void
    {
        $allowed   = $this->buildClass();
        $forbidden = $this->buildClass();

        $allowed->addMethodToAllowList('newQuery');

        static::assertTrue($allowed->isAllowToExecute('newQuery'));
        static::assertFalse($forbidden->isAllowToExecute('newQuery'));
    }

    #[Test]
    public function a_method_that_was_never_disallowed_is_allowed(): void
    {
        static::assertTrue($this->buildClass()->isAllowToExecute('save'));
    }

    #[Test]
    public function a_null_hint_falls_back_to_the_generic_message(): void
    {
        $class = $this->buildClass();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage("Method Not Allowed: You don't allow to execute this method!");

        $class->hintless();
    }

    #[Test]
    public function the_allow_list_helpers_are_chainable(): void
    {
        $class = $this->buildClass();

        static::assertSame($class, $class->addMethodToAllowList('newQuery'));
        static::assertSame($class, $class->removeMethodFromAllowList('newQuery'));
    }

    /**
     * The guarded model under test. Typed as the anonymous class itself so the analyser
     * sees the trait's methods rather than the bare Model.
     */
    private function buildClass(): Model&GuardedModelContract
    {
        return new class extends Model implements GuardedModelContract {
            use AllowToExecute;

            protected static function booting(): void
            {
                static::addMethodToDisallowMap('delete', 'Remover::exec($model)');
                static::addMethodToDisallowMap('newQuery', 'new Query($model)');
                static::addMethodToDisallowMap(
                    'handle',
                    static fn() => throw new MethodNotAllowedException("QQ")
                );
                static::addMethodToDisallowMap('hintless');
            }

            public function delete()
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__);
            }

            public function handle(mixed $value, mixed $value2): mixed
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__, $value, $value2);
            }

            public function newQuery()
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__);
            }

            public function hintless(): mixed
            {
                return $this->checkPossibilityAndExecute(__FUNCTION__);
            }
        };
    }
}

/**
 * Surface of {@see AllowToExecute} the tests drive, so PHPStan can see it on the anonymous
 * model rather than only on Illuminate\Database\Eloquent\Model.
 */
interface GuardedModelContract
{
    public function addMethodToAllowList(string $method): static;

    public function removeMethodFromAllowList(string $method): static;

    public function isAllowToExecute(string $method): bool;

    public function handle(mixed $value, mixed $value2): mixed;

    public function hintless(): mixed;
}
