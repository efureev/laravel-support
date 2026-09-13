<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit\Helpers;

use Php\Support\Laravel\Helpers\PostgresArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PostgresArrayTest extends TestCase
{
    /**
     * @return iterable<string, array{array<array-key, mixed>, string}>
     */
    public static function encodeProvider(): iterable
    {
        yield 'empty'          => [
            [],
            '{}',
        ];
        yield 'single'         => [
            ['php'],
            '{php}',
        ];
        yield 'many'           => [
            [
                'php',
                'laravel',
            ],
            '{php,laravel}',
        ];
        yield 'ints'           => [
            [
                1,
                2,
                3,
            ],
            '{1,2,3}',
        ];
        yield 'floats'         => [
            [
                1.5,
                2.25,
            ],
            '{1.5,2.25}',
        ];
        yield 'nested'         => [
            [
                [
                    1,
                    2,
                ],
                [3],
            ],
            '{{1,2},{3}}',
        ];
        yield 'keys dropped'   => [
            [
                'a' => 'x',
                'b' => 'y',
            ],
            '{x,y}',
        ];
        yield 'nested keys'    => [
            ['a' => ['b' => 1]],
            '{{1}}',
        ];
        yield 'unicode kept'   => [
            ['ключ'],
            '{ключ}',
        ];
        yield 'bools as ints'  => [
            [
                true,
                false,
            ],
            '{true,false}',
        ];
    }

    /**
     * @param array<array-key, mixed> $input
     */
    #[Test]
    #[DataProvider('encodeProvider')]
    public function it_encodes_to_a_postgres_array_literal(array $input, string $expected): void
    {
        self::assertSame($expected, PostgresArray::encode($input));
    }

    /**
     * @return iterable<string, array{string|null, array<int, mixed>}>
     */
    public static function decodeProvider(): iterable
    {
        yield 'null'        => [
            null,
            [],
        ];
        yield 'empty string' => [
            '',
            [],
        ];
        yield 'empty array' => [
            '{}',
            [],
        ];
        yield 'single'      => [
            '{php}',
            ['php'],
        ];
        yield 'many'        => [
            '{php,laravel}',
            [
                'php',
                'laravel',
            ],
        ];
        yield 'ints cast'   => [
            '{1,2,3}',
            [
                1,
                2,
                3,
            ],
        ];
        yield 'floats cast' => [
            '{1.5,2.25}',
            [
                1.5,
                2.25,
            ],
        ];
        yield 'nested'      => [
            '{{1,2},{3}}',
            [
                [
                    1,
                    2,
                ],
                [3],
            ],
        ];
        yield 'quoted'      => [
            '{"a b","c d"}',
            [
                'a b',
                'c d',
            ],
        ];
        yield 'unicode'     => [
            '{ключ,значение}',
            [
                'ключ',
                'значение',
            ],
        ];
        yield 'not a literal' => [
            'notanarray',
            [],
        ];
    }

    /**
     * @param array<int, mixed> $expected
     */
    #[Test]
    #[DataProvider('decodeProvider')]
    public function it_decodes_a_postgres_array_literal(?string $input, array $expected): void
    {
        self::assertSame($expected, PostgresArray::decode($input));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function roundTripProvider(): iterable
    {
        yield 'strings' => [
            [
                'php',
                'laravel',
                'support',
            ],
        ];
        yield 'ints'    => [
            [
                1,
                2,
                3,
            ],
        ];
        yield 'nested'  => [
            [
                [
                    1,
                    2,
                ],
                [3],
            ],
        ];
        yield 'unicode' => [
            [
                'ключ',
                'значение',
            ],
        ];
    }

    /**
     * @param array<array-key, mixed> $input
     */
    #[Test]
    #[DataProvider('roundTripProvider')]
    public function it_round_trips(array $input): void
    {
        self::assertSame($input, PostgresArray::decode(PostgresArray::encode($input)));
    }

    #[Test]
    public function it_drops_string_keys_recursively(): void
    {
        self::assertSame([1, [2]], PostgresArray::toIndexedArray(['a' => 1, 'b' => ['c' => 2]]));
        self::assertSame([], PostgresArray::toIndexedArray([]));
    }

    #[Test]
    public function it_reports_where_a_nested_literal_ended(): void
    {
        $end = null;
        PostgresArray::decode('{{1,2},{3}}', 0, $end);

        self::assertSame(10, $end, 'The closing brace of the outer literal is at offset 10.');
    }

    #[Test]
    public function documented_pitfall_elements_containing_a_comma_do_not_round_trip(): void
    {
        // Elements are emitted unquoted, so a comma inside a value splits it in two.
        // Documented in docs/postgres-array.md — use a jsonb column when values may contain it.
        self::assertSame('{a,b}', PostgresArray::encode(['a,b']));
        self::assertSame(['a', 'b'], PostgresArray::decode('{a,b}'));
    }
}
