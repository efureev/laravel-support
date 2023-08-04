# Pagination

## PaginatedResourceArray

Helps you organize a nested resource collection with paginator for a resource.

### Example

```php
<?php

use Illuminate\Http\Resources\Json\JsonResource;
use Php\Support\Laravel\Pagination\PaginatedResourceArray;

class FolderResource extends JsonResource
{
    public function toArray($request)
    {
        $files = new PaginatedResourceArray(new FileCollection($this->files()->paginate()));

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'files'       => $files->toArray($request),
        ];
    }
}
```

Json will be like

```javascript
{
    "data": {
        "id": "d2dc4265-bfc4-4a3e-b6f1-cbff02680233",
        "title": "foto albumn",
        "files": {
            "data": [
                {
                    "id": "bd542a2c-6d5f-4253-b89f-be2008380bce",
                    "mime": "image/jpeg",
                    "url": ".../wbcacD8QcExrPFRn8j0DHl6BnTeAe1N6UYipsPB8.jpeg"
                },
                {
                    "id": "3f29ecc3-f62f-4fc9-92d8-74b2d6a7918a",
                    "mime": "image/png",
                    "url": ".../sSRwJ1FSxEmfCtG7e6fJJvkGJFeOn24cf5HyPRby.png"
                }
            ],
            "links": {
                "first": "http://.../d2dc4265-bfc4-4a3e-b6f1-cbff02680233?page=1",
                "last": "http://.../d2dc4265-bfc4-4a3e-b6f1-cbff02680233?page=1",
                "prev": null,
                "next": null
            },
            "meta": {
                "current_page": 1,
                "from": 1,
                "last_page": 1,
                "path": "http://.../d2dc4265-bfc4-4a3e-b6f1-cbff02680233",
                "per_page": 15,
                "to": 2,
                "total": 2
            }
        }
    }
}
```
