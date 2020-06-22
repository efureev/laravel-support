<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Pagination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class PaginatedResourceArray
 * @package Sitesoft\Alice\Modules\Mediateca
 *
 * Modify paginator-resource in an array
 */
class PaginatedResourceArray
{
    /** @var ResourceCollection|mixed */
    protected $resource;

    /**
     * @param ResourceCollection|mixed $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Create an array that represents the object.
     *
     * @param Request $request
     *
     * @return array
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

    /**
     * Add the pagination information to the response.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function paginationInformation($request): array
    {
        $paginated = $this->resource->resource->toArray($request);

        return [
            'links' => $this->paginationLinks($paginated),
            'meta'  => $this->meta($paginated),
        ];
    }

    /**
     * Get the pagination links for the response.
     *
     * @param array $paginated
     *
     * @return array
     */
    protected function paginationLinks($paginated): array
    {
        return [
            'first' => $paginated['first_page_url'] ?? null,
            'last'  => $paginated['last_page_url'] ?? null,
            'prev'  => $paginated['prev_page_url'] ?? null,
            'next'  => $paginated['next_page_url'] ?? null,
        ];
    }

    /**
     * Gather the meta data for the response.
     *
     * @param array $paginated
     *
     * @return array
     */
    protected function meta($paginated): array
    {
        return Arr::except(
            $paginated,
            [
                'data',
                'first_page_url',
                'last_page_url',
                'prev_page_url',
                'next_page_url',
            ]
        );
    }

    /**
     * Wrap the given data if necessary.
     *
     * @param $data
     * @param array $with
     * @param array $additional
     *
     * @return array
     */
    protected function wrap($data, $with = [], $additional = []): array
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        }

        if ($this->haveDefaultWrapperAndDataIsUnwrapped($data)) {
            $data = [$this->wrapper() => $data];
        } elseif ($this->haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)) {
            $data = [($this->wrapper() ?? 'data') => $data];
        }

        return array_merge_recursive($data, $with, $additional);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped($data): bool
    {
        return $this->wrapper() && !array_key_exists($this->wrapper(), $data);
    }

    /**
     * Determine if "with" data has been added and our data is unwrapped.
     *
     * @param array $data
     * @param array $with
     * @param array $additional
     *
     * @return bool
     */
    protected function haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional): bool
    {
        return (!empty($with) || !empty($additional)) &&
            (!$this->wrapper() ||
                !array_key_exists($this->wrapper(), $data));
    }

    protected function wrapper(): ?string
    {
        return $this->resource::$wrap;
    }
}
