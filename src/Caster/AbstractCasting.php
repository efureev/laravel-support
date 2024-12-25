<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Php\Support\Exceptions\Exception;
use Php\Support\Helpers\Json;
use Php\Support\Traits\ConfigurableTrait;

abstract class AbstractCasting implements Caster, Jsonable, Arrayable
{
    use ConfigurableTrait;

    protected const EMPTY_JSON_OBJECT = '{}';

    protected const EMPTY_JSON_ARRAY = '[]';

    /**
     * AbstractCasting constructor.
     *
     * @param  string|array|static  $value
     *
     * @throws Exception
     */
    public function __construct($value = null)
    {
        $this->fill($value);
    }

    /**
     * Fill the instance with data
     *
     * @param  string|array|static  $value
     *
     * @return AbstractCasting
     * @throws Exception
     */
    public function fill($value): self
    {
        $value = $this->preFormat($value);

        if (empty($value)) {
            return $this->afterFill();
        }

        $value = $this->convert($value);

        $this->validate($value);

        return $this
            ->configurable($value)
            ->afterFill();
    }

    /**
     * Prepend formatting
     *
     * @param  string|array|static  $value
     *
     * @return string|array|static
     */
    protected function preFormat($value)
    {
        return $value;
    }

    protected function afterFill(): self
    {
        return $this;
    }

    /**
     * Convert value data
     *
     * @param  mixed  $value
     *
     * @return array|string
     */
    public function convert($value)
    {
        if (is_string($value)) {
            $value = static::dataFromJson($value);
        }

        if ($value instanceof static) {
            $value = $value->toArray();
        }

        return $value;
    }

    /**
     * @param  mixed  $value
     *
     * @throws Exception
     */
    public function validate($value)
    {
        if (!is_array($value)) {
            throw new Exception('type of value must be Array');
        }
    }

    /**
     * JSON-string to Array
     *
     * @param  string|null|array  $json
     *
     * @return array
     */
    protected static function dataFromJson($json): array
    {
        if (empty($json)) {
            return [];
        }

        if (is_array($json)) {
            return $json;
        }

        return Json::decode($json);
    }

    /**
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * @param  static|array|null  $value
     *
     * @return string|null
     */
    public static function castToDatabase($value): ?string
    {
        if ($value instanceof static) {
            return $value->toJson();
        }

        return static::dataToJson($value ?? []);
    }

    /**
     * @param  int  $options
     *
     * @return string|null
     */
    public function toJson($options = 320): ?string
    {
        return static::dataToJson($this->toArray(), $options);
    }

    /**
     * Array to JSON-string
     *
     * @param  array  $data
     * @param  int  $options  Default is `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
     *
     * @return string|null
     */
    protected static function dataToJson(array $data, $options = 320): ?string
    {
        if (empty($data)) {
            return static::emptyJsonStruct();
        }

        return Json::encode($data, $options);
    }

    /**
     * @param  string|null  $value
     *
     * @return $this
     * @throws Exception
     */
    public function castFromDatabase(?string $value): self
    {
        return $this->fill($value);
    }

    /**
     * @param $value
     * @param $original
     *
     * @return bool
     */
    public static function isEquivalent($value, $original): bool
    {
        return $value->toJson() === $original?->toJson();
    }

    protected static function emptyJsonStruct(): ?string
    {
        return self::EMPTY_JSON_OBJECT;
    }

    public function value(): mixed
    {
        return static::castToDatabase($this);
    }
}
