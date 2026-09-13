# Validation rules

## Delimited

`Php\Support\Laravel\Rules\Delimited` validates every item of a delimited string against another
rule, and adds count, uniqueness and trimming constraints on top.

It implements [`ValidationRule`](https://laravel.com/docs/13.x/validation#using-rule-objects) and
sets `$implicit = true`, so it runs even when the attribute is empty — otherwise `min()` could
never reject an empty value.

### Constructor

```
__construct(string|array|ValidationRule $rule)
```

`$rule` is applied to each item. Anything Laravel's validator accepts works: a rule string
(`'email'`), a composite string (`'email|max:20'`), an array of rules, or a rule object.

### Fluent constraints

| Method | Effect |
|---|---|
| `min(int $minimum): static` | fail when fewer than `$minimum` non-empty items are present |
| `max(int $maximum): static` | fail when more than `$maximum` items are present |
| `allowDuplicates(bool $allowed = true): static` | permit repeated items (rejected by default) |
| `separatedBy(string $separator): static` | split on `$separator` instead of `,`; throws `InvalidArgumentException` on an empty separator |
| `doNotTrimItems(): static` | keep surrounding whitespace on each item |
| `maxItemLength(int $length): static` | fail when any single item is longer than `$length` characters; throws `InvalidArgumentException` for a non-positive length |
| `validationMessageWord(string $word): static` | the noun used in the count messages (`item` by default) |

### Recipes

A comma-separated list of between one and three unique e-mail addresses:

```php
use Php\Support\Laravel\Rules\Delimited;

$request->validate([
    'recipients' => [(new Delimited('email'))->min(1)->max(3)],
]);
```

Semicolon-separated tags, duplicates allowed, message worded as "tags":

```php
use Php\Support\Laravel\Rules\Delimited;

$rule = (new Delimited('string|max:30'))
    ->separatedBy(';')
    ->allowDuplicates()
    ->validationMessageWord('tag')
    ->max(10);
```

### Messages

Published under the `laravelSupport` translation namespace, in `en` and `ru`:

| Key | English |
|---|---|
| `laravelSupport::messages.delimited.min` | `You must specify at least :min :item` |
| `laravelSupport::messages.delimited.max` | `You can only specify :max :item` |
| `laravelSupport::messages.delimited.unique` | `You may not specify duplicates.` |
| `laravelSupport::messages.delimited.item_length` | `Each :item may be at most :max characters long` |

`:min` / `:max` carry the boundary, `:item` the pluralised noun, and `:actual` — available to
your own overrides — the number of items actually supplied. `:item` is pluralised from the
**boundary**, not from the actual count, so a `min(2)` failure reads "at least 2 items" even
when one was given.

Override them by publishing the files:

```bash
php artisan vendor:publish --tag=laravel-support-lang
```

### Pitfalls

* Empty items are dropped before counting. `'a@b.com, ,c@d.com'` is two items, not three.
* With `doNotTrimItems()` the surrounding whitespace becomes part of the item, so
  `'a@b.com, c@d.com'` fails an `email` rule on the second item.

## Authorized

`Php\Support\Laravel\Rules\Authorized` passes when the authenticated user is allowed to perform
`$ability` on the model whose primary key is the value under validation.

```
__construct(string $ability, string $className)
```

```php
use Php\Support\Laravel\Rules\Authorized;

$request->validate([
    'post_id' => ['required', new Authorized('update', Post::class)],
]);
```

It fails — with the same message every time — when nobody is logged in, when no row matches the
key, or when the [gate](https://laravel.com/docs/13.x/authorization#via-the-user-model) denies
the ability. The message comes from `laravelSupport::messages.authorized` and receives
`:attribute`, `:ability` and `:className` (the class basename).

### Pitfall

The three failure modes are deliberately indistinguishable, so the response does not leak
whether a given id exists. If you need to tell them apart, validate existence separately with
[`exists`](https://laravel.com/docs/13.x/validation#rule-exists).

## HasValidate

`Php\Support\Laravel\Rules\HasValidate` is a trait of static helpers for one-off validation and
for reading typed values out of a request.

| Method | Returns |
|---|---|
| `validateValue(mixed $value, string $rules, string $attributeName = 'value', ?string $message = null): mixed` | `$value`, or throws `ValidationException` |
| `validateValues(array $values, array $rules, array $messages = []): array` | `$values`, or throws `ValidationException` |
| `gainIntValue(Request $request, string $name, ?int $default = null): ?int` | the input as `int` |
| `gainBoolValue(Request $request, string $name, ?bool $default = null): ?bool` | the input as `bool` |
| `gainFloatValue(Request $request, string $name, ?float $default = null): ?float` | the input as `float` |
| `gainStringValue(Request $request, string $name, ?string $default = null): ?string` | the input as `string` |
| `gainArrayValue(Request $request, string $name, ?array $default = null): ?array` | the input as a list, wrapping a scalar |

```php
use Php\Support\Laravel\Rules\HasValidate;

final class TagService
{
    use HasValidate;

    public function find(string $id): Tag
    {
        return Tag::findOrFail(static::validateValue($id, 'required|uuid'));
    }
}
```

`gainBoolValue()` reads strings the HTML-form way through
[`FILTER_VALIDATE_BOOLEAN`](https://www.php.net/manual/en/filter.filters.validate.php): `"1"`,
`"true"`, `"on"` and `"yes"` are true and everything else — including unparseable strings — is
false. An absent or explicitly null value returns `null`, so "not sent" stays distinguishable
from "sent as false".

### Pitfalls

`gainIntValue()` treats a zero as absent, because it falls back with `?:`. A request carrying
`n=0` with a default of `7` yields `7`, and with no default yields `null` — never `0`. Read
[`$request->integer($name)`](https://laravel.com/docs/13.x/requests#retrieving-input) when zero
is a meaningful value.

`gainStringValue()` behaves the same way for the empty string: `s=` with a default of `'def'`
yields `null`, not `'def'` and not `''`. `gainFloatValue()` mirrors `gainIntValue()`, zero
included.
