<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses;

use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;
use Php\Support\Laravel\Traits\ModelQueryable;

class HasModel
{
    use ModelQueryable;

    public static function modelClass(): string
    {
        return BaseModel::class;
    }
}
