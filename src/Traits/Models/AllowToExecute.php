<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Exceptions\MethodNotAllowedException;

/**
 * Forbid selected model methods and point callers at the supported way instead.
 *
 * ```php
 * class Invoice extends Model
 * {
 *     use AllowToExecute;
 *
 *     protected static function booting(): void
 *     {
 *         static::addMethodToDisallowMap('delete', 'InvoiceVoider::void($invoice)');
 *     }
 *
 *     public function delete(): mixed
 *     {
 *         return $this->checkPossibilityAndExecute(__FUNCTION__);
 *     }
 * }
 * ```
 *
 * @mixin Model
 */
trait AllowToExecute
{
    /** @var array<string, string|Closure|null> method => hint */
    protected static array $disallowMethodsMap = [];

    /** @var array<string, true> methods re-enabled on this instance */
    protected array $allowList = [];

    /**
     * Re-enable a disallowed method on this instance only.
     */
    public function addMethodToAllowList(string $method): static
    {
        $this->allowList[$method] = true;

        return $this;
    }

    /**
     * Undo {@see self::addMethodToAllowList()} — the method falls back to the class-wide rule.
     */
    public function removeMethodFromAllowList(string $method): static
    {
        unset($this->allowList[$method]);

        return $this;
    }

    /**
     * Whether `$method` may run on this instance right now.
     */
    public function isAllowToExecute(string $method): bool
    {
        return !array_key_exists($method, static::$disallowMethodsMap) || isset($this->allowList[$method]);
    }

    /**
     * Run `parent::$method(...)` when allowed, otherwise apply the registered hint.
     *
     * A `Closure` hint is invoked with `$this` and its return value is passed through — use it
     * to redirect the caller. A string hint becomes part of the exception message.
     *
     * @throws MethodNotAllowedException when the method is disallowed and the hint is not a Closure
     */
    protected function checkPossibilityAndExecute(string $method, mixed ...$arguments): mixed
    {
        if ($this->isAllowToExecute($method)) {
            return parent::$method(...$arguments);
        }

        $hint = static::$disallowMethodsMap[$method];

        if ($hint instanceof Closure) {
            return $hint($this);
        }

        throw new MethodNotAllowedException(
            is_string($hint)
                ? "You should call this method like this: $hint"
                : 'You don\'t allow to execute this method!'
        );
    }

    /**
     * Forbid `$method` for every instance of this class.
     *
     * @param string|Closure|null $hint how to call it instead, or a Closure to run in its place
     */
    protected static function addMethodToDisallowMap(string $method, string|Closure|null $hint = null): void
    {
        static::$disallowMethodsMap[$method] = $hint;
    }

    /**
     * @param array<string, string|Closure|null> $methods method => hint
     */
    protected static function addMethodsToDisallowMap(array $methods): void
    {
        foreach ($methods as $method => $hint) {
            static::$disallowMethodsMap[$method] = $hint;
        }
    }

    /**
     * Lift the class-wide ban on `$method`.
     */
    protected static function removeMethodFromDisallowMap(string $method): void
    {
        unset(static::$disallowMethodsMap[$method]);
    }
}
