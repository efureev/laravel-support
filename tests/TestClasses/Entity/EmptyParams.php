<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Entity;

use Php\Support\Laravel\Caster\AbstractCasting;

/**
 * Class EmptyParams
 *
 * @property string $key
 * @property array $config
 */
class EmptyParams extends AbstractCasting
{
    protected $key;

    protected $config = [];

    public function toArray(): array
    {
        $data = [];

        if (!empty($this->key)) {
            $data['key'] = $this->key;
        }

        if (!empty($this->config)) {
            $data['config'] = $this->config;
        }

        return $data;
    }
}
