<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Entity;

use Php\Support\Laravel\Caster\AbstractCasting;

/**
 * Class ModuleStatus
 *
 * Class for module status
 */
class Status extends AbstractCasting
{

    public const STATUS_NOT_INSTALLED = 'not_installed';
    public const STATUS_INSTALLED     = 'installed';
    public const STATUS_ERROR         = 'error';

    public const STATUSES = [
        self::STATUS_NOT_INSTALLED,
        self::STATUS_INSTALLED,
        self::STATUS_ERROR,
    ];

    protected $key;
    protected $title;

    public function convert($value)
    {
        return [
            'key'   => $value,
            'title' => __('statuses.' . $value),
        ];
    }


    /**
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'key'   => $this->key,
            'title' => $this->title,
        ];
    }

    public static function castToDatabase($value): ?string
    {
        if ($value instanceof static) {
            return $value->key();
        }

        return $value;
    }
}
