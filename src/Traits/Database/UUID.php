<?php


namespace Php\Support\Laravel\Traits\Database;

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
     * @param bool|callable|Expression $default
     *
     * @return ColumnDefinition
     * @throws Exception
     */
    protected static function columnUUID(
        Blueprint $table,
        string $name = 'id',
        $default = true
    ): ColumnDefinition {
        $defCol = $table->uuid($name);

        if (!$default) {
            return $defCol;
        }

        $driverName = DB::getDriverName();

        switch ($driverName) {
            case 'pgsql':
                DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
                $expression = 'uuid_generate_v4()';
                break;
            case 'mysql':
                $expression = 'UUID()';
                break;
            default:
                throw new Exception('Your DB driver [' . $driverName . '] does not supported');
        }

        if (is_callable($default)) {
            $defaultExpression = new Expression($default($driverName));
        } elseif ($default instanceof Expression) {
            $defaultExpression = $default;
        } elseif ($default === true) {
            $defaultExpression = new Expression($expression);
        } else {
            $defaultExpression = new Expression($default);
        }

        return $defCol->default($defaultExpression);
    }
}
