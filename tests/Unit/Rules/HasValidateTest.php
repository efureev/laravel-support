<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit\Rules;

use Illuminate\Http\Request;
use Php\Support\Laravel\Rules\HasValidate;
use Php\Support\Laravel\Tests\Unit\AbstractUnitTestCase;

class HasValidateTest extends AbstractUnitTestCase
{
    use HasValidate;

    public function testGainValue(): void
    {
        $query = [
            'val_int'    => 12,
            'val_string' => 'test',
            'val_bool'   => true,
        ];

        $request = new Request($query);

        static::assertEquals(12, static::gainIntValue($request, 'val_int'));
        static::assertEquals('test', static::gainStringValue($request, 'val_string'));
        static::assertTrue(static::gainBoolValue($request, 'val_bool'));
    }

    public function testGainValueNull(): void
    {
        $request = new Request();

        static::assertNull(static::gainIntValue($request, 'val_int'));
        static::assertNull(static::gainIntValue($request, 'val_int', null));
        static::assertNull(static::gainStringValue($request, 'val_string'));
        static::assertNull(static::gainStringValue($request, 'val_string', null));
        static::assertNull(static::gainBoolValue($request, 'val_bool'));
        static::assertNull(static::gainBoolValue($request, 'val_bool', null));
    }

    public function testGainValueDefault(): void
    {
        $request = new Request();

        static::assertEquals(15, static::gainIntValue($request, 'val_int', 15));
        static::assertEquals(0, static::gainIntValue($request, 'val_int', 0));
        static::assertEquals('example', static::gainStringValue($request, 'val_string', 'example'));
        static::assertEquals('', static::gainStringValue($request, 'val_string', ''));
        static::assertFalse(static::gainBoolValue($request, 'val_bool', false));
        static::assertTrue(static::gainBoolValue($request, 'val_bool', true));
    }

}
