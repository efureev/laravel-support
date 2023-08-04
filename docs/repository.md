# Repository

## Example

```php
<?php

declare(strict_types=1);

use Php\Support\Laravel\Repositories\AbstractRepository;

final class ContentRepository extends AbstractRepository
{
    protected $modelClass = CustomEloquentModel::class;
}
```
