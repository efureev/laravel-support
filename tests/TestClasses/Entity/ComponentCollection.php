<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Entity;

use Php\Support\Laravel\Caster\AbstractCastingCollection;

class ComponentCollection extends AbstractCastingCollection
{
    protected function wrapEntity(): ?callable
    {
        return static function ($item) {
            return new Component($item);
        };
    }
}
