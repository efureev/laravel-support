<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Delimited implements Rule
{
    /** @var string|array|Rule */
    protected $rule;

    protected $minimum;

    protected $maximum;

    protected $allowDuplicates = false;

    protected $message = '';

    protected $separatedBy = ',';

    /** @var bool */
    protected $trimItems = true;

    /** @var string */
    protected $validationMessageWord = 'item';

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @param int $minimum
     *
     * @return $this
     */
    public function min(int $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * @param int $maximum
     *
     * @return $this
     */
    public function max(int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function allowDuplicates(bool $allowed = true): self
    {
        $this->allowDuplicates = $allowed;

        return $this;
    }

    public function separatedBy(string $separator): self
    {
        $this->separatedBy = $separator;

        return $this;
    }

    public function doNotTrimItems(): self
    {
        $this->trimItems = false;

        return $this;
    }

    public function validationMessageWord(string $word): self
    {
        $this->validationMessageWord = $word;

        return $this;
    }

    public function passes($attribute, $value)
    {
        if ($this->trimItems) {
            $value = trim($value);
        }

        $items = collect(explode($this->separatedBy, $value))
            ->filter(
                static function ($item) {
                    return (string)$item !== '';
                }
            );

        if (($this->minimum !== null) && $items->count() < $this->minimum) {
            $this->message = __(
                'laravelSupport::messages.delimited.min',
                [
                    'minimum' => $this->minimum,
                    'actual'  => $items->count(),
                    'item'    => Str::plural($this->validationMessageWord, $items->count()),
                ]
            );

            return false;
        }

        if (($this->maximum !== null) && $items->count() > $this->maximum) {
            $this->message = __(
                'laravelSupport::messages.delimited.max',
                [
                    'maximum' => $this->maximum,
                    'actual'  => $items->count(),
                    'item'    => Str::plural($this->validationMessageWord, $items->count()),
                ]
            );

            return false;
        }

        if ($this->trimItems) {
            $items = $items->map(
                static function (string $item) {
                    return trim($item);
                }
            );
        }

        foreach ($items as $item) {
            [
                $isValid,
                $validationMessage,
            ] = $this->validate($attribute, $item);

            if (!$isValid) {
                $this->message = $validationMessage;

                return false;
            }
        }

        if (!$this->allowDuplicates && $items->unique()->count() !== $items->count()) {
            $this->message = __('laravelSupport::messages.delimited.unique');

            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }

    protected function validate(string $attribute, string $item): array
    {
        $attribute = Str::after($attribute, '.');

        $validator = Validator::make([$attribute => $item], [$attribute => $this->rule]);

        return [
            $validator->passes(),
            $validator->getMessageBag()->first($attribute),
        ];
    }
}
