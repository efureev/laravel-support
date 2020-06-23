<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Enumerable;
use Php\Support\Exceptions\Exception;
use Php\Support\Exceptions\JsonException;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;

abstract class AbstractCastingCollection implements Caster, Arrayable, Jsonable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    public function __construct($value = null)
    {
        $this->fill($value);
    }

    public function fill($value): self
    {
        $value = $this->preFormat($value);

        if (empty($value)) {
            return $this;
        }

        $value = $this->convert($value);

        $this->validate($value);

        return $this
            ->push(...$value)
            ->afterFill();
    }

    /**
     * Prepend formatting
     *
     * @param string|array|static $value
     *
     * @return string|array|static
     */
    protected function preFormat($value)
    {
        return $value;
    }

    /**
     * Convert value data into array
     *
     * @param mixed $value
     *
     * @return array|string
     */
    public function convert($value)
    {
        if (is_string($value)) {
            $value = static::dataFromJson($value);
        } elseif ($value instanceof static) {
            $value = $value->toArray();
        } else {
            $value = $this->getArrayableItems($value);
        }

        return $value;
    }

    /**
     * JSON-string to Array
     *
     * @param string|null $json
     *
     * @return array
     * @throws JsonException
     */
    protected static function dataFromJson($json): array
    {
        if (empty($json)) {
            return [];
        }

        return Json::decode($json);
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     *
     * @return array|mixed|null
     * @throws \Php\Support\Exceptions\JsonException
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }
        if ($items instanceof Enumerable) {
            return $items->all();
        }
        if ($items instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $items->toArray();
        }
        if ($items instanceof Jsonable) {
            return Json::decode($items->toJson());
        }
        if ($items instanceof \JsonSerializable) {
            return (array)$items->jsonSerialize();
        }
        if ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    protected function afterFill(): self
    {
        return $this;
    }

    /**
     * @param mixed $value
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
     * @param static|array $value
     *
     * @return string|null
     */
    public static function castToDatabase($value): ?string
    {
        if ($value instanceof Jsonable) {
            return $value->toJson();
        }

        return static::dataToJson($value);
    }

    /**
     * @param int $options
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
     * @param array $data
     * @param int $options Default is `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
     *
     * @return string|null
     */
    protected static function dataToJson(array $data, $options = 320): ?string
    {
        if (empty($data)) {
            return '[]';
        }

        return Json::encode($data, $options);
    }

    /**
     * @param string|null $value
     *
     * @return $this
     * @throws Exception
     */
    public function castFromDatabase(?string $value): self
    {
        return $this->fill($value);
    }

    /**
     * Push one or more items onto the end of the collection.
     *
     * @param mixed $values [optional]
     *
     * @return $this
     */
    public function push(...$values): self
    {
        foreach ($values as $value) {
            $this->add($value);
        }

        return $this;
    }

    /**
     * Add an item to the collection.
     *
     * @param mixed $item
     *
     * @return $this
     */
    public function add($item): self
    {
        $this->items[] = with($item, $this->wrapEntity());

        return $this;
    }

    /**
     * Wrapper for element
     * @return callable|null
     *  protected function wrapEntity(): ?callable {
     *      return static function ($item) {return $item;};
     *  }
     * @example
     */
    protected function wrapEntity(): ?callable
    {
        return null;
    }

    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}
