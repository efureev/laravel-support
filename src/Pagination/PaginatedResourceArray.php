<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Pagination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Renders a paginated resource collection as a plain array, so it can be nested inside another
 * resource instead of becoming the HTTP response body.
 *
 * ```php
 * class FolderResource extends JsonResource
 * {
 *     public function toArray($request): array
 *     {
 *         return [
 *             'id'    => $this->id,
 *             'files' => (new PaginatedResourceArray(
 *                 new FileCollection($this->files()->paginate())
 *             ))->toArray($request),
 *         ];
 *     }
 * }
 * ```
 *
 * Extends {@see PaginatedResourceResponse} rather than reimplementing it, so `links` / `meta`,
 * the `$wrap` / `$forceWrapping` rules and the resource's own `paginationInformation()` hook all
 * behave exactly as they do in a normal paginated response.
 *
 * @see https://laravel.com/docs/13.x/eloquent-resources#pagination
 */
class PaginatedResourceArray extends PaginatedResourceResponse
{
    public function __construct(ResourceCollection $resource)
    {
        parent::__construct($resource);
    }

    /**
     * The array a `PaginatedResourceResponse` would have encoded as JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->wrap(
            $this->resource->resolve($request),
            array_merge_recursive(
                $this->paginationInformation($request),
                $this->resource->with($request),
                $this->resource->additional
            )
        );
    }
}
