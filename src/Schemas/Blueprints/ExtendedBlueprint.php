<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Schemas\Blueprints;

use Illuminate\Database\Schema\Blueprint;

/**
 * Class ExtendedBlueprint
 * @package Php\Support\Laravel
 */
class ExtendedBlueprint extends Blueprint
{
    /**
     * @param string $column
     * @param int $length
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function bit(string $column, int $length)
    {
        return $this->addColumn('bit', $column, compact('length'));
    }
}
