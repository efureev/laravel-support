<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Entity;

use Php\Support\Interfaces\Arrayable;
use Php\Support\Traits\ConfigurableTrait;

class Component implements Arrayable
{
    use ConfigurableTrait;

    /** @var string */
    public $module;

    /** @var array */
    public $params = [];

    /** @var bool */
    public $isMain = false;


    public function __construct($attributes)
    {
        $this->configurable($attributes);
    }

    public function toArray(): array
    {
        return ['name' => $this->module];
    }
}
