# Repository

`Php\Support\Laravel\Repositories\AbstractRepository` is a thin base for an Eloquent repository.
Name the model and you have the common operations.

```php
use Php\Support\Laravel\Repositories\AbstractRepository;

final class PostRepository extends AbstractRepository
{
    protected string $modelClass = Post::class;
}
```

## Methods

| Method | Does |
|---|---|
| `all(): Collection` | every row |
| `with(array\|string $relations): Builder` | a query with the given [eager loads](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading) |
| `findModel(mixed $id, bool $throw = true): ?Model` | resolve by key, or pass a Model straight through |
| `store(array $attributes, mixed $model = null): Model` | update `$model`, or create a row when it is null |
| `delete(mixed $id): void` | delete the row identified by `$id` |

Protected seams for subclasses: `query()`, `setModel()`, `createModel()`, `storeModel()`,
`deleteModel()`.

## Recipes

```php
$repository = new PostRepository();

$repository->all();                                  // Collection<Post>
$repository->findModel(42);                          // Post
$repository->findModel('missing', throw: false);     // null
$repository->store(['title' => 'New']);              // creates
$repository->store(['title' => 'Renamed'], 42);      // updates #42
$repository->delete(42);
```

## Behaviour

`findModel()` accepts an `int` or `string` key, or an already-loaded Model — which it returns
untouched, saving the query. Anything else raises
[`InvalidParamException`](exceptions.md#invalidparamexception).

With `$throw` left at `true`, a key that matches nothing raises Laravel's
[`ModelNotFoundException`](https://laravel.com/docs/13.x/eloquent#not-found-exceptions), and the
id you looked up is preserved on the exception (`$e->getIds()`) and in its message.

### Pitfall

`$modelClass` is instantiated with `new`, so the model's constructor must work without
arguments — which is the case for every Eloquent model.
