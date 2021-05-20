<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

use Exception;
use Illuminate\Support\Collection as BaseCollection;
use Php\Support\Helpers\Json;

trait HasCasts
{
    public function setAttribute($key, $value)
    {
        if ($value instanceof Caster) {
            $this->attributes[$key] = $value::castToDatabase($value);

            return $this;
        }

        $type = $this->getCastType($key);

        if ($this->typeIsClass($type) && method_exists($type, 'castToDatabase')) {
            $this->attributes[$key] = $type::castToDatabase($value);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    protected function getCastType($key): ?string
    {
        if (!$type = $this->getCasts()[$key] ?? null) {
            return null;
        }

        if ($this->isCustomDateTimeCast($type)) {
            return 'custom_datetime';
        }

        if ($this->isDecimalCast($type)) {
            return 'decimal';
        }

        return $type;
    }

    public function typeIsClass(?string $type): bool
    {
        return $type !== null && class_exists($type);
    }

    protected function castAttribute($key, $value)
    {
        if ($value === null) {
            return $value;
        }

        $type = $this->getCastType($key);
        if ($type === null) {
            return $value;
        }

        return $this->castValue($type, $value);
    }

    public function castValue($type, $value)
    {
        switch ($simpleType = strtolower(trim($type))) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'real':
            case 'float':
            case 'double':
                return $this->fromFloat($value);
            case 'decimal':
                return $this->asDecimal($value, explode(':', $simpleType, 2)[1]);
            case 'string':
                return (string)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'object':
                return $this->fromJson($value, true);
            case 'array':
            case 'json':
                return $this->fromJson($value);
            case 'collection':
                return new BaseCollection($this->fromJson($value));
            case 'date':
                return $this->asDate($value);
            case 'datetime':
            case 'custom_datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimestamp($value);
        }


        if ($this->typeIsClass($type)) {
            $type = new $type();
            if (!$type instanceof Caster) {
                throw new Exception('Invalid class for casting');
            }
            return $type->castFromDatabase($value);
        }

        return $value;
    }

    public function originalIsEquivalent($key): bool
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $type = $this->getCastType($key);

        if ($this->typeIsClass($type)) {
            $castType = $this->$key;
            if (is_array($castType) && method_exists($type, 'isEquivalent')) {
                return $type::isEquivalent($castType, $this->getOriginal($key));
            }

            if ($castType instanceof Caster) {
                return $castType::isEquivalent($castType, $this->getOriginal($key));
            }
        }

        return parent::originalIsEquivalent($key);
    }

    /**
     * @inheritDoc
     */
    public function fromJson($value, $asObject = false)
    {
        return Json::decode($value, !$asObject);
    }

    /**
     * @inheritDoc
     */
    protected function asJson($value)
    {
        return Json::encode($value);
    }
}
