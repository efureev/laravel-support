<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Database\Eloquent\Model;
use Php\Support\Exceptions\MethodNotAllowedException;

/**
 * @mixin Model
 */
trait AllowToExecute
{
    protected static array $disallowMethodsMap = [];
    protected array $allowList = [];

    public function addMethodToAllowList(string $method): void
    {
        $this->allowList[$method] = true;
    }

    private function rejectMethodFromAllowList(string $method): void
    {
        unset($this->allowList[$method]);
    }

    protected function isAllowToExecute(string $method): bool
    {
        return !array_key_exists($method, static::$disallowMethodsMap) || isset($this->allowList[$method]);
    }

    protected function checkPossibilityAndExecute(string $method, ...$arguments): mixed
    {
        if ($this->isAllowToExecute($method)) {
            return parent::$method(...$arguments);
        }
        $hint = static::$disallowMethodsMap[$method];

        if ($hint instanceof \Closure) {
            return $hint($this);
        }

        if (is_string($hint)) {
            $text = "You should call this method like this: $hint";
        }

        throw new MethodNotAllowedException($text ?? 'You don\'t allow to execute this method!');
    }

    protected static function addMethodToDisallowMap(string $method, string|\Closure|null $hint = null): void
    {
        static::$disallowMethodsMap[$method] = $hint;
    }

    protected static function addMethodsToDisallowMap(array $methods): void
    {
        foreach ($methods as $method => $hint) {
            static::$disallowMethodsMap[$method] = $hint;
        }
    }
}
