<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits;

use Php\Support\Traits\Maker;

/**
 * Class ModuleStatus
 *
 * @package Sitesoft\Hub\Modules\Entity
 */
trait Caster
{
    use Maker;


    /**
     * @param mixed $value
     *
     * @return Caster
     */
    public static function cast(...$value): self
    {
        return self::make(...$value);
    }
}
