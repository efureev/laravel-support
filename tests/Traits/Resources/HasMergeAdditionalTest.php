<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Traits\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Traits\Resources\HasMergeAdditional;
use PHPUnit\Framework\Attributes\Test;

class HasMergeAdditionalTest extends AbstractTestCase
{
    #[Test]
    public function repeated_calls_accumulate_instead_of_replacing(): void
    {
        // JsonResource::additional() assigns, so the second call would drop the first payload.
        $resource = (new MergingResource(['id' => 1]))
            ->additional(['meta' => 'a'])
            ->additional(['extra' => 'b']);

        self::assertSame(['meta' => 'a', 'extra' => 'b'], $resource->additional);
    }

    #[Test]
    public function the_framework_default_replaces_which_is_the_difference(): void
    {
        $resource = (new PlainResource(['id' => 1]))
            ->additional(['meta' => 'a'])
            ->additional(['extra' => 'b']);

        self::assertSame(['extra' => 'b'], $resource->additional);
    }

    #[Test]
    public function a_later_call_overwrites_the_same_key(): void
    {
        $resource = (new MergingResource(['id' => 1]))
            ->additional(['meta' => 'a'])
            ->additional(['meta' => 'b']);

        self::assertSame(['meta' => 'b'], $resource->additional);
    }

    #[Test]
    public function it_is_chainable(): void
    {
        $resource = new MergingResource(['id' => 1]);

        self::assertSame($resource, $resource->additional(['meta' => 'a']));
    }

    #[Test]
    public function the_merged_data_reaches_the_response(): void
    {
        $response = (new MergingResource(['id' => 1]))
            ->additional(['meta' => 'a'])
            ->additional(['extra' => 'b'])
            ->toResponse(Request::create('/'));

        self::assertSame(
            ['data' => ['id' => 1], 'meta' => 'a', 'extra' => 'b'],
            $response->getData(true)
        );
    }
}

class PlainResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return (array)$this->resource;
    }
}

class MergingResource extends PlainResource
{
    use HasMergeAdditional;
}
