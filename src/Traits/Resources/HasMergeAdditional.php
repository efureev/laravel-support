<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Resources;

/**
 * Makes `JsonResource::additional()` accumulate instead of replace.
 *
 * The framework's version assigns, so a second call drops whatever the first one added. With
 * this trait repeated calls merge, later keys winning.
 *
 * ```php
 * $resource->additional(['meta' => $meta])->additional(['links' => $links]);
 * ```
 *
 * @see https://laravel.com/docs/13.x/eloquent-resources#adding-meta-data
 *
 * @mixin \Illuminate\Http\Resources\Json\JsonResource
 */
trait HasMergeAdditional
{
    /**
     * @param array<string, mixed> $data
     */
    public function additional(array $data): static
    {
        $this->additional = array_merge($this->additional, $data);

        return $this;
    }
}
