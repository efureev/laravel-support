<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Http;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

class LaraRequest extends Request
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;


    /**
     * Set the container implementation.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container): self
    {
        $this->container = $container;

        return $this->afterInit();
    }

    protected function afterInit(): static
    {
        return $this;
    }
}
