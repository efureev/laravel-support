# Model traits

## Modelable

`Php\Support\Laravel\Traits\Modelable` binds an Eloquent model into a class that is not itself a
model — a form request, a service, a value object.

```php
use Php\Support\Laravel\Traits\Modelable;

final class TagFinder
{
    use Modelable;

    protected static function modelClass(): string
    {
        return Tag::class;
    }
}
```

| Method | Returns |
|---|---|
| `modelClass(): string` | **abstract** — the model FQCN |
| `getModelInstance(array $attributes = []): Model` | a fresh, unsaved instance |
| `model(bool $new = false): Model` | the memoised instance; `true` forces a new one |
| `modelKeyName(): string` | the key **qualified with its table** (`tags.id`) — for WHERE clauses |
| `modelInputKeyName(): string` | the key **unqualified** (`id`) — the name an outside source uses |

## ModelQueryable

`Php\Support\Laravel\Traits\ModelQueryable` builds on `Modelable` and resolves the model from a
key the using class supplies.

Declare `modelKeyValueGainer(): callable` — a factory returning `fn(string $keyName): mixed` that
reads the value from wherever it lives.

| Method | Returns |
|---|---|
| `modelKeyValue(): mixed` | the key the gainer reports; raises `UnknownMethodException` when no gainer is declared |
| `newQueryWithoutScopes(): Builder` | a query on the bound model with no global scopes |
| `findModelQuery(mixed $modelId = null): Builder` | a query scoped to `$modelId`, or to the gainer's key |
| `findModelOrFail(mixed $modelId = null): Model` | the first match, or `ModelNotFoundException` |

An array or `Arrayable` key produces a `whereIn`, a scalar a `where`.

## RequestModelable

`Php\Support\Laravel\Traits\Requests\RequestModelable` is `ModelQueryable` with the gainer wired
to request input.

```php
use Illuminate\Foundation\Http\FormRequest;
use Php\Support\Laravel\Traits\Requests\RequestModelable;

class UpdateTagRequest extends FormRequest
{
    use RequestModelable;

    protected static function modelClass(): string
    {
        return Tag::class;
    }
}

// in the controller
$tag = $request->findModelOrFail();   // reads `id` from the request
```

The gainer receives the **unqualified** key name, because a dot in
[`$request->input()`](https://laravel.com/docs/13.x/requests#retrieving-input) means nested
access — a qualified `tags.id` would look for `['tags' => ['id' => …]]` and find nothing. Override
`modelInputKeyName()` when the request field is named differently from the column.

## AllowToExecute

`Php\Support\Laravel\Traits\Models\AllowToExecute` forbids selected model methods and points
callers at the supported path instead.

```php
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Traits\Models\AllowToExecute;

class Invoice extends Model
{
    use AllowToExecute;

    protected static function booting(): void
    {
        static::addMethodToDisallowMap('delete', 'InvoiceVoider::void($invoice)');
    }

    public function delete(): mixed
    {
        return $this->checkPossibilityAndExecute(__FUNCTION__);
    }
}

$invoice->delete();
// MethodNotAllowedException: Method Not Allowed: You should call this method like this:
// InvoiceVoider::void($invoice)
```

| Method | Does |
|---|---|
| `addMethodToDisallowMap(string $method, string\|Closure\|null $hint = null): void` | forbid `$method` class-wide |
| `addMethodsToDisallowMap(array $methods): void` | forbid several at once |
| `removeMethodFromDisallowMap(string $method): void` | lift the class-wide ban |
| `addMethodToAllowList(string $method): static` | re-enable it on this instance only |
| `removeMethodFromAllowList(string $method): static` | undo that |
| `isAllowToExecute(string $method): bool` | whether it may run right now |
| `checkPossibilityAndExecute(string $method, mixed ...$args): mixed` | run `parent::$method()`, or apply the hint |

A `Closure` hint is invoked with `$this` and its value is returned, so it can redirect the caller.
A string hint becomes the exception message. `null` falls back to a generic message.

### Pitfalls

* **`checkPossibilityAndExecute()` calls `parent::`, not `self::`.** The guarded method has to
  override a real parent method — that is what `delete()` and `newQuery()` do above.
* **The disallow map is static.** It is shared by every instance of the class. The allow list is
  per-instance and is the only per-object escape hatch.
