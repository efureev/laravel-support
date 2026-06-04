<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Class Authorized
 * @package Php\Support\Laravel\Rules
 */
class Authorized implements ValidationRule
{
    public function __construct(
        protected readonly string $ability,
        protected readonly string $className,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$user = Auth::user()) {
            $fail($this->getErrorMessage($attribute));
            return;
        }

        if (!$model = $this->className::find($value)) {
            $fail($this->getErrorMessage($attribute));
            return;
        }

        if (!$user->can($this->ability, $model)) {
            $fail($this->getErrorMessage($attribute));
        }
    }

    protected function getErrorMessage(string $attribute): string
    {
        $classBasename = class_basename($this->className);

        return __(
            'laravelSupport::messages.authorized',
            [
                'attribute' => $attribute,
                'ability'   => $this->ability,
                'className' => $classBasename,
            ]
        );
    }
}
