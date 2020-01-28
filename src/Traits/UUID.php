<?php


namespace Php\Support\Laravel\Traits;

use Exception;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\DB;

trait UUID
{
    /**
     * @param Blueprint $table
     * @param string $name
     * @param bool $withOutDefault
     *
     * @return ColumnDefinition
     * @throws Exception
     */
    protected static function columnUUID(Blueprint $table, string $name, $withOutDefault = false): ColumnDefinition
    {
        $expression = '';
        switch (DB::getDriverName()) {
            case 'pgsql':
                DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
                $expression = 'uuid_generate_v4()';
                break;
            case 'mysql':
                $expression = 'UUID()';
                break;
            default:
                throw new Exception('Your DB driver [' . DB::getDriverName() . '] does not supported');
                break;
        }

        $defCol = $table->uuid($name);

        return $withOutDefault ? $defCol : $defCol->default(new Expression($expression));
    }
}
