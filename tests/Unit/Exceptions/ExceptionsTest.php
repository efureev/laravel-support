<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit\Exceptions;

use BadMethodCallException;
use LogicException;
use Php\Support\Laravel\Exceptions\InvalidParamException;
use Php\Support\Laravel\Exceptions\MethodNotAllowedException;
use Php\Support\Laravel\Exceptions\UnknownMethodException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExceptionsTest extends TestCase
{
    #[Test]
    public function invalid_param_exception_keeps_the_spl_ancestry(): void
    {
        // Consumers that used to catch efureev/support's LogicException-based exception
        // keep working after the dependency was dropped.
        self::assertInstanceOf(LogicException::class, new InvalidParamException());
    }

    #[Test]
    public function invalid_param_exception_builds_a_message(): void
    {
        self::assertSame('Invalid parameter', (new InvalidParamException())->getMessage());
        self::assertSame('Invalid parameter: id', (new InvalidParamException(null, 'id'))->getMessage());
        self::assertSame('custom', (new InvalidParamException('custom'))->getMessage());
        self::assertSame('id', (new InvalidParamException(null, 'id'))->name);
    }

    #[Test]
    public function unknown_method_exception_keeps_the_spl_ancestry(): void
    {
        self::assertInstanceOf(BadMethodCallException::class, new UnknownMethodException('A::b'));
    }

    #[Test]
    public function unknown_method_exception_builds_a_message(): void
    {
        self::assertSame('Unknown method: A::b', (new UnknownMethodException('A::b'))->getMessage());
        self::assertSame('custom', (new UnknownMethodException('A::b', 'custom'))->getMessage());
        self::assertSame('A::b', (new UnknownMethodException('A::b'))->method);
    }

    #[Test]
    public function method_not_allowed_exception_keeps_the_spl_ancestry(): void
    {
        self::assertInstanceOf(RuntimeException::class, new MethodNotAllowedException('nope'));
    }

    #[Test]
    public function method_not_allowed_exception_builds_a_message(): void
    {
        self::assertSame('Method Not Allowed: nope', (new MethodNotAllowedException('nope'))->getMessage());
        self::assertSame('Denied: nope', (new MethodNotAllowedException('nope', 'Denied'))->getMessage());
        self::assertSame('nope', (new MethodNotAllowedException('nope', ''))->getMessage());
        self::assertSame('nope', (new MethodNotAllowedException('nope'))->reason);
    }
}
