# Service providers

`Php\Support\Laravel\ServiceProviders\AbstractServiceProvider` is a base for a package's own
service provider. It aggregates five traits and routes `boot()` to a runtime-specific hook.

```php
use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;

class BillingServiceProvider extends AbstractServiceProvider
{
    public const PACKAGE_NS = 'billing';

    protected static array $policies = [Invoice::class => InvoicePolicy::class];
    protected static array $commands = [SendInvoices::class];

    public function register(): void
    {
        $this->registerService(Billing::class, self::PACKAGE_NS, singleton: true);
    }

    protected function beforeBoot(): void
    {
        $this->registerConfig(self::PACKAGE_NS)
            ->registerTranslations(self::PACKAGE_NS);
    }

    protected function bootForServer(): void
    {
        $this->registerRoutes(['api', 'web'])
            ->registerPolicies();
    }

    protected function bootForConsole(): void
    {
        $this->registerCommands();
    }

    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}
```

`packageSourcePath()` is the only thing you must supply: every path helper is derived from it.
Return `__DIR__` from a file inside the package's `src/`.

The traits it aggregates — `PackageNames`, `HasPolicies`, `HasCommands`, `HasBooting` and
`HasRegisters` — can also be used one at a time on a provider of your own.

## Boot sequence

```
boot()
 ├─ availableToBoot()  ── false ⇒ nothing below runs
 ├─ beforeBoot()
 ├─ bootPackageForTesting | bootPackageForConsole | bootPackageForServer
 └─ afterBoot()
```

Which middle hook fires depends on the runtime, in this order: the `testing` environment wins,
then [`runningInConsole()`](https://laravel.com/docs/13.x/packages), otherwise the server path.
`HasBooting` owns that decision and memoises it per provider instance.

| Hook | Default behaviour | Override to |
|---|---|---|
| `availableToBoot(): bool` | `true` | disable the package by config or licence |
| `beforeBoot(): void` | nothing | register config and translations |
| `bootForServer(): void` | nothing | routes, policies, view composers |
| `bootForConsole(): void` | nothing | commands, publishes |
| `bootForTesting(): void` | nothing | test-only bindings |
| `afterBoot(): void` | nothing | anything that needs the rest in place |

`bootPackageForConsole()` registers migrations and commands before calling `bootForConsole()`.
`bootPackageForTesting()` runs the server path, then the console path when running in console,
then `bootForTesting()`.

`availableToBoot()` is consulted once per provider instance and also gates
`callBootingCallbacks()` / `callBootedCallbacks()`, so a disabled package registers nothing at
all.

## HasRegisters

| Method | Does |
|---|---|
| `registerConfig(array\|string $configs, bool $needReplace = false): static` | merge `config/<name>.php` into the app config, or replace it |
| `registerRoutes(array\|string $routes): static` | load `routes/<name>.php` |
| `registerTranslations(string $namespace): static` | load `resources/lang` under `$namespace` |
| `registerViews(string $namespace): static` | load `resources/views` under `$namespace` |
| `registerService(string $class, Closure\|string\|null $name = null, bool $singleton = false, ?string $alias = null): static` | bind a service, optionally aliased or as a singleton |
| `registerInstance(string $abstract, mixed $instance, ?string $alias = null): static` | bind an existing object |
| `registerMigrations(): static` | load `database/migrations` unless disabled |
| `onEvent(string\|array $event, Closure\|string $callback): static` | [`Event::listen()`](https://laravel.com/docs/13.x/events) |
| `ignoreMigrations(): void` / `runMigrations(bool $enable = true): void` | toggle migration loading |

`registerConfig()` and `registerRoutes()` raise `RuntimeException` naming the file when it does
not exist, rather than passing `null` into the framework.

`registerConfig()` accepts a map to load a file under a different key:

```php
$this->registerConfig(['connections' => 'billing.connections']);
```

## HasPolicies and HasCommands

Declare them as static properties and register them in the right hook:

```php
protected static array $policies = [Invoice::class => InvoicePolicy::class];
protected static array $commands = [SendInvoices::class];
```

`registerPolicies()` / `registerCommands()` register the declared sets;
`registerPoliciesForce(array)` / `registerCommandsForce(array)` take an ad-hoc set.

### Pitfall

[`ServiceProvider::commands()`](https://laravel.com/docs/13.x/packages#commands) defers through
`Artisan::starting()`, so commands registered after the console application has been built never
appear. Register them from `bootForConsole()`, not later.

## HasPathHelpers

All of these resolve relative to `packageSourcePath()`'s parent and return `null` when the
directory or file is not readable.

| Method | Resolves to |
|---|---|
| `packageRootPath(): string` | the package root |
| `packagePath(string $path): ?string` | an arbitrary path under the root |
| `getMigrationsPath(): ?string` | `database/migrations` |
| `getDatabaseSeedersPath(?string $path = null): ?string` | `database/seeders` |
| `getConfigPath(?string $path = null): ?string` | `config` |
| `getRoutesPath(?string $path = null): ?string` | `routes` |
| `getResourcesPath(?string $path = null): ?string` | `resources` |
| `getTranslationsPath(): ?string` | `resources/lang` |
| `getViewsPath(): ?string` | `resources/views` |
| `version(): string` | `version` from `version.json`, falling back to `composer.json` |

## PackageNames

| Method | Returns |
|---|---|
| `getPackageNamespace(): string` | the provider's namespace, or `packageNamespace()` when declared |
| `getPackageName(): string` | the `PACKAGE_NS` constant when declared, otherwise the provider's FQCN |
