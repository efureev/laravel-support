# Exceptions

The package throws three exceptions of its own. Each extends the SPL exception that best
describes the failure, so generic `catch` blocks keep working.

| Class | Extends | Thrown when |
|---|---|---|
| `Php\Support\Laravel\Exceptions\InvalidParamException` | `LogicException` | an argument has a type the callee cannot work with |
| `Php\Support\Laravel\Exceptions\UnknownMethodException` | `BadMethodCallException` | a required method is missing on the calling class |
| `Php\Support\Laravel\Exceptions\MethodNotAllowedException` | `RuntimeException` | a method exists but the current state forbids calling it |

## InvalidParamException

```
__construct(?string $message = null, ?string $name = null)
```

`$name` names the offending parameter and is readable afterwards as `$e->name`. With neither
argument the message is `Invalid parameter`; with `$name` only it becomes
`Invalid parameter: <name>`.

## UnknownMethodException

```
__construct(string $method, ?string $message = null)
```

`$method` is the fully-qualified method that was expected, readable as `$e->method`. The default
message is `Unknown method: <method>`.

## MethodNotAllowedException

```
__construct(string $reason, string $message = 'Method Not Allowed')
```

The final message is `"<message>: <reason>"`, or just `<reason>` when `$message` is an empty
string. `$reason` is readable as `$e->reason`.

## Upgrading from 4.x

These classes used to come from `efureev/support` under the `Php\Support\Exceptions\` namespace.
Catches on the SPL parents are unaffected:

```php
try {
    $repository->findModel($id);
} catch (\LogicException $e) {
    // still catches InvalidParamException
}
```

Catches on the old FQCNs are not. See [Upgrading to 5.0](upgrade-5.0.md).
