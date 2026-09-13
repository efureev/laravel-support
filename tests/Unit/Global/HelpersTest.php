<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit\Global;

use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    #[Test]
    public function to_collect_wraps_a_scalar(): void
    {
        $result = toCollect('x');

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame(['x'], $result->all());
    }

    #[Test]
    public function to_collect_wraps_an_array_element_wise(): void
    {
        self::assertSame(['a', 'b'], toCollect(['a', 'b'])->all());
    }

    #[Test]
    public function to_collect_returns_an_existing_collection_untouched(): void
    {
        $collection = collect(['a']);

        self::assertSame($collection, toCollect($collection));
    }

    #[Test]
    public function to_collect_returns_an_eloquent_collection_untouched(): void
    {
        $collection = new EloquentCollection([new BaseModel()]);

        self::assertSame($collection, toCollect($collection));
    }

    #[Test]
    public function to_collect_keeps_a_model_whole(): void
    {
        $model = new BaseModel(['name' => 'x']);

        self::assertSame([$model], toCollect($model)->all());
    }

    #[Test]
    public function to_collect_differs_from_collection_wrap_on_null(): void
    {
        // The two documented differences from Collection::wrap(). If either stops holding,
        // the helper has no reason to exist any more.
        self::assertSame([null], toCollect(null)->all());
        self::assertSame([], Collection::wrap(null)->all());
    }

    #[Test]
    public function to_collect_differs_from_collection_wrap_on_identity(): void
    {
        $collection = collect(['a']);

        self::assertSame($collection, toCollect($collection));
        self::assertNotSame($collection, Collection::wrap($collection));
    }

    #[Test]
    public function object_to_array_unwraps_arrayable(): void
    {
        $arrayable = new class implements Arrayable {
            public function toArray(): array
            {
                return ['a' => 1];
            }
        };

        self::assertSame(['a' => 1], objectToArray($arrayable));
    }

    #[Test]
    public function object_to_array_unwraps_json_serializable(): void
    {
        $serializable = new class implements \JsonSerializable {
            /** @return array<string, mixed> */
            public function jsonSerialize(): array
            {
                return ['b' => 2];
            }
        };

        self::assertSame(['b' => 2], objectToArray($serializable));
    }

    #[Test]
    public function object_to_array_unwraps_traversable(): void
    {
        self::assertSame(['c' => 3], objectToArray(new ArrayIterator(['c' => 3])));
    }

    #[Test]
    public function object_to_array_recurses(): void
    {
        $inner = new class implements Arrayable {
            public function toArray(): array
            {
                return ['deep' => new ArrayIterator(['x' => 1])];
            }
        };

        self::assertSame(['outer' => ['deep' => ['x' => 1]]], objectToArray(['outer' => $inner]));
    }

    #[Test]
    public function object_to_array_passes_scalars_through(): void
    {
        self::assertSame('x', objectToArray('x'));
        self::assertSame(5, objectToArray(5));
        self::assertNull(objectToArray(null));
    }

    #[Test]
    public function object_to_array_leaves_an_object_it_cannot_unwrap_alone(): void
    {
        $plain    = new \stdClass();
        $plain->a = 1;

        self::assertSame($plain, objectToArray($plain));
    }

    #[Test]
    public function arrayable_wins_over_json_serializable(): void
    {
        $both = new class implements Arrayable, \JsonSerializable {
            public function toArray(): array
            {
                return ['from' => 'arrayable'];
            }

            /** @return array<string, mixed> */
            public function jsonSerialize(): array
            {
                return ['from' => 'json'];
            }
        };

        self::assertSame(['from' => 'arrayable'], objectToArray($both));
    }
}
