# Resources and pagination

## PaginatedResourceArray

`Php\Support\Laravel\Pagination\PaginatedResourceArray` renders a paginated resource collection
as a plain array, so it can be **nested inside another resource** instead of becoming the HTTP
response body.

```
__construct(ResourceCollection $resource)
toArray(Illuminate\Http\Request $request): array
```

```php
use Illuminate\Http\Resources\Json\JsonResource;
use Php\Support\Laravel\Pagination\PaginatedResourceArray;

class FolderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'files' => (new PaginatedResourceArray(
                new FileCollection($this->files()->paginate())
            ))->toArray($request),
        ];
    }
}
```

The nested value carries the same `data` / `links` / `meta` shape a
[paginated resource response](https://laravel.com/docs/13.x/eloquent-resources#pagination) would
have produced:

```json
{
  "data": {
    "id": "d2dc4265",
    "title": "photo album",
    "files": {
      "data": [{ "id": "bd542a2c", "mime": "image/jpeg" }],
      "links": { "first": "…?page=1", "last": "…?page=1", "prev": null, "next": null },
      "meta": { "current_page": 1, "from": 1, "last_page": 1, "per_page": 15, "to": 2, "total": 2 }
    }
  }
}
```

It extends `Illuminate\Http\Resources\Json\PaginatedResourceResponse` rather than reimplementing
it, so `$wrap`, `additional()` and the resource's own `paginationInformation()` hook all behave
exactly as they do in a normal paginated response. A test compares the output against
`parent::toResponse($request)->getData(true)` on every run, so a change in the framework's shape
fails the build instead of drifting silently.

### Pitfall

`links` and `meta` count as "with" data, so the payload is always wrapped — even for a resource
whose `$wrap` is `null`, in which case the default `data` key is used.

## HasMergeAdditional

`Php\Support\Laravel\Traits\Resources\HasMergeAdditional` makes
[`JsonResource::additional()`](https://laravel.com/docs/13.x/eloquent-resources#adding-meta-data)
accumulate instead of replace.

```
additional(array $data): static
```

```php
class PostResource extends JsonResource
{
    use HasMergeAdditional;
}

(new PostResource($post))
    ->additional(['meta' => $meta])
    ->additional(['links' => $links]);   // both survive
```

Without the trait the second call discards `meta`. Keys collide the usual way: a later call wins.
