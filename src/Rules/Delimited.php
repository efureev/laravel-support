<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Delimited implements ValidationRule
{
    /**
     * Run the validation rule even when the attribute is empty/absent,
     * so that the "min" constraint can reject empty values.
     */
    public bool $implicit = true;

    protected ?int $minimum = null;

    protected ?int $maximum = null;

    protected bool $allowDuplicates = false;

    protected string $separatedBy = ',';

    protected bool $trimItems = true;

    protected string $validationMessageWord = 'item';

    /**
     * @param string|array|ValidationRule $rule
     */
    public function __construct(
        protected string|array|ValidationRule $rule
    ) {
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

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->trimItems) {
            $value = trim((string)$value);
        }

        $items = collect(explode($this->separatedBy, (string)$value))
            ->filter(
                static function ($item) {
                    return (string)$item !== '';
                }
            );

        if (($this->minimum !== null) && $items->count() < $this->minimum) {
            $fail(
                __(
                    'laravelSupport::messages.delimited.min',
                    [
                        'minimum' => $this->minimum,
                        'actual'  => $items->count(),
                        'item'    => Str::plural($this->validationMessageWord, $items->count()),
                    ]
                )
            );

            return;
        }

        if (($this->maximum !== null) && $items->count() > $this->maximum) {
            $fail(
                __(
                    'laravelSupport::messages.delimited.max',
                    [
                        'maximum' => $this->maximum,
                        'actual'  => $items->count(),
                        'item'    => Str::plural($this->validationMessageWord, $items->count()),
                    ]
                )
            );

            return;
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
            ] = $this->validateItem($attribute, $item);

            if (!$isValid) {
                $fail($validationMessage);

                return;
            }
        }

        if (!$this->allowDuplicates && $items->unique()->count() !== $items->count()) {
            $fail(__('laravelSupport::messages.delimited.unique'));
        }
    }

    protected function validateItem(string $attribute, string $item): array
    {
        $attribute = Str::after($attribute, '.');

        $validator = Validator::make([$attribute => $item], [$attribute => $this->rule]);

        return [
            $validator->passes(),
            $validator->getMessageBag()->first($attribute),
        ];
    }
}
