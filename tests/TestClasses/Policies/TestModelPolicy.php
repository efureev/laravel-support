<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;

class TestModelPolicy
{
    use HandlesAuthorization;

    public function edit(User $user, TestModel $testModel): bool
    {
        return $testModel->user->getKey() === $user->getKey();
    }
}
