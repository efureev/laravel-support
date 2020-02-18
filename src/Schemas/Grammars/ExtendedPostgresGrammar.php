<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Schemas\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Fluent;

/**
 * Class ExtendedPostgresGrammar
 * @package Php\Support\Laravel
 */
class ExtendedPostgresGrammar extends PostgresGrammar
{
    /**
     * Create the column definition for a 'bit' type.
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeBit(Fluent $column): string
    {
        return "bit({$column->length})";
    }
}
