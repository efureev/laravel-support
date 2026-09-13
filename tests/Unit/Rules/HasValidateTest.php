<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit\Rules;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Php\Support\Laravel\Rules\HasValidate;
use Php\Support\Laravel\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public static function boolProvider(): iterable
    {
        yield 'string 1'     => [
            '1',
            true,
        ];
        yield 'string true'  => [
            'true',
            true,
        ];
        yield 'string on'    => [
            'on',
            true,
        ];
        yield 'string yes'   => [
            'yes',
            true,
        ];
        yield 'string 0'     => [
            '0',
            false,
        ];
        yield 'string false' => [
            'false',
            false,
        ];
        yield 'string off'   => [
            'off',
            false,
        ];
        yield 'garbage'      => [
            'banana',
            false,
        ];
        yield 'empty string' => [
            '',
            false,
        ];
        yield 'int 1'        => [
            1,
            true,
        ];
        yield 'int 0'        => [
            0,
            false,
        ];
        yield 'real bool'    => [
            true,
            true,
        ];
    }

    #[DataProvider('boolProvider')]
    public function testGainBoolValueInterpretsFormValues(mixed $input, bool $expected): void
    {
        $request = new Request(['flag' => $input]);

        static::assertSame($expected, static::gainBoolValue($request, 'flag'));
    }

    public function testValidateValueReturnsTheValueWhenItPasses(): void
    {
        static::assertSame('9f2a...', static::validateValue('9f2a...', 'required|string'));
        static::assertSame(5, static::validateValue(5, 'required|integer'));
    }

    public function testValidateValueThrowsWhenItFails(): void
    {
        $this->expectException(ValidationException::class);

        static::validateValue('not-a-uuid', 'required|uuid');
    }

    public function testValidateValueUsesTheCustomMessage(): void
    {
        try {
            static::validateValue('x', 'required|uuid', 'id', 'The id is malformed.');
            static::fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            static::assertSame('The id is malformed.', $e->validator->errors()->first('id'));
        }
    }

    public function testValidateValueNamesTheAttribute(): void
    {
        try {
            static::validateValue('x', 'required|uuid', 'order_id');
            static::fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            static::assertArrayHasKey('order_id', $e->validator->errors()->toArray());
        }
    }

    public function testValidateValuesReturnsTheInputWhenItPasses(): void
    {
        $values = [
            'id'   => 5,
            'name' => 'x',
        ];

        static::assertSame(
            $values,
            static::validateValues($values, ['id' => 'required|integer', 'name' => 'required|string'])
        );
    }

    public function testValidateValuesThrowsWhenItFails(): void
    {
        $this->expectException(ValidationException::class);

        static::validateValues(['id' => 'x'], ['id' => 'required|integer']);
    }

    public function testValidateValuesUsesCustomMessages(): void
    {
        try {
            static::validateValues(['id' => 'x'], ['id' => 'required|integer'], ['id.integer' => 'Nope.']);
            static::fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            static::assertSame('Nope.', $e->validator->errors()->first('id'));
        }
    }

    /**
     * Pins the behaviour documented as a pitfall in docs/rules.md: the `?:` fallback makes a
     * falsy value indistinguishable from an absent one.
     */
    public function testGainIntValueTreatsZeroAsAbsent(): void
    {
        $request = new Request(['n' => '0']);

        static::assertSame(7, static::gainIntValue($request, 'n', 7));
        static::assertNull(static::gainIntValue($request, 'n'));
    }

    public function testGainStringValueTreatsAnEmptyStringAsAbsent(): void
    {
        $request = new Request(['s' => '']);

        static::assertNull(static::gainStringValue($request, 's', 'def'));
        static::assertNull(static::gainStringValue($request, 's'));
    }

    public function testGainFloatValue(): void
    {
        $request = new Request(['f' => '1.5', 'i' => 2]);

        static::assertSame(1.5, static::gainFloatValue($request, 'f'));
        static::assertSame(2.0, static::gainFloatValue($request, 'i'));
        static::assertNull(static::gainFloatValue($request, 'missing'));
        static::assertSame(9.5, static::gainFloatValue($request, 'missing', 9.5));
    }

    public function testGainArrayValueWrapsAScalar(): void
    {
        $request = new Request(['one' => 'a', 'many' => ['a', 'b']]);

        static::assertSame(['a'], static::gainArrayValue($request, 'one'));
        static::assertSame(['a', 'b'], static::gainArrayValue($request, 'many'));
    }

    public function testGainArrayValueFallsBackToTheDefault(): void
    {
        $request = new Request();

        static::assertNull(static::gainArrayValue($request, 'missing'));
        static::assertSame(['x'], static::gainArrayValue($request, 'missing', ['x']));
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
