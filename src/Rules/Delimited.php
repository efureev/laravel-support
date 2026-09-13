<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

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

    /** @var non-empty-string */
    protected string $separatedBy = ',';

    protected bool $trimItems = true;

    protected string $validationMessageWord = 'item';

    protected ?int $maximumItemLength = null;

    /**
     * @param string|array<array-key, mixed>|ValidationRule $rule the rule each item must satisfy
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
    public function min(int $minimum): static
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * @param int $maximum
     *
     * @return $this
     */
    public function max(int $maximum): static
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function allowDuplicates(bool $allowed = true): static
    {
        $this->allowDuplicates = $allowed;

        return $this;
    }

    /**
     * @throws InvalidArgumentException when the separator is empty — `explode()` cannot split on it
     */
    public function separatedBy(string $separator): static
    {
        if ($separator === '') {
            throw new InvalidArgumentException('The separator must not be empty.');
        }

        $this->separatedBy = $separator;

        return $this;
    }

    public function doNotTrimItems(): static
    {
        $this->trimItems = false;

        return $this;
    }

    /**
     * Reject the whole value when any single item is longer than `$length` characters.
     *
     * Counts characters, not bytes. A `max:` rule on the item would do the same, but only when
     * the item rule is a string you control — this works with a rule object too.
     *
     * @throws InvalidArgumentException when the length is not positive
     */
    public function maxItemLength(int $length): static
    {
        if ($length < 1) {
            throw new InvalidArgumentException('The maximum item length must be at least 1.');
        }

        $this->maximumItemLength = $length;

        return $this;
    }

    public function validationMessageWord(string $word): static
    {
        $this->validationMessageWord = $word;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $items = collect(explode($this->separatedBy, (string)$value));

        if ($this->trimItems) {
            $items = $items->map(static fn(string $item): string => trim($item));
        }

        $items = $items->filter(static fn(string $item): bool => $item !== '')->values();

        if (($this->minimum !== null) && $items->count() < $this->minimum) {
            $fail(
                __(
                    'laravelSupport::messages.delimited.min',
                    [
                        'min'    => $this->minimum,
                        'actual' => $items->count(),
                        'item'   => Str::plural($this->validationMessageWord, $this->minimum),
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
                        'max'    => $this->maximum,
                        'actual' => $items->count(),
                        'item'   => Str::plural($this->validationMessageWord, $this->maximum),
                    ]
                )
            );

            return;
        }

        foreach ($items as $item) {
            if (($this->maximumItemLength !== null) && mb_strlen($item) > $this->maximumItemLength) {
                $fail(
                    __(
                        'laravelSupport::messages.delimited.item_length',
                        [
                            'max'    => $this->maximumItemLength,
                            'actual' => mb_strlen($item),
                            'item'   => $this->validationMessageWord,
                        ]
                    )
                );

                return;
            }

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

    /**
     * @return array{bool, string} whether the item passed, and the first failure message
     */
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
