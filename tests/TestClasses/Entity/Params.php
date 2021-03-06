<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Entity;

use Php\Support\Laravel\Caster\AbstractCasting;

/**
 * Class Params
 *
 * Параметры Page
 *
 * @property string $key
 * @property string $testParam
 * @property array $config
 */
class Params extends AbstractCasting
{
    protected $key;

    protected $config = [];

    protected $testParam;

    public function toArray(): array
    {
        return [
            'key'       => $this->key,
            'config'    => $this->config,
            'testParam' => $this->testParam,
        ];
    }

    protected static function emptyJsonStruct(): ?string
    {
        return self::EMPTY_JSON_ARRAY;
    }
}
