<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Traits\Database\UUID;

class CreateUuidTable extends Migration
{
    use UUID;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'uuid_table',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();

                $table->string('title')->nullable();
            }
        );


        Schema::create(
            'uuid_table2',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();
                static::columnUUID($table, 'table_id', null)->index();
                $table->foreign('table_id')->references('id')->on('uuid_table')->onDelete('cascade');
            }
        );

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
                $expression = '';
        }

        Schema::create(
            'uuid_table3',
            static function (Blueprint $table) use ($expression) {
                static::columnUUID($table)->primary();
                static::columnUUID($table, 'table_id', new Expression($expression))->index();
                $table->foreign('table_id')->references('id')->on('uuid_table');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uuid_table');
        Schema::dropIfExists('uuid_table2');
        Schema::dropIfExists('uuid_table3');
    }

}
