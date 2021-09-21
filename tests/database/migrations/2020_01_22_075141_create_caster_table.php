<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Traits\Database\UUID;

class CreateCasterTable extends Migration
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
            'users',
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->string('remember_token');
                $table->timestamps();
            }
        );

        Schema::create(
            'test_table',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();

                $table->string('title')->nullable();
                $table->boolean('enabled')->default(false);
                $table->jsonb('params')->nullable();
                $table->string('status')->nullable();
                $table->jsonb('config')->nullable();
                $table->string('str')->nullable();
                $table->string('str_empty')->nullable();
                $table->integer('int')->default(0);
                $table->integer('user_id')->nullable();
            }
        );

        DB::statement('ALTER TABLE test_table ADD COLUMN geo_point point');

        Schema::create(
            'pg_table',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();
                $table->string('title')->nullable();
            }
        );

        DB::statement('ALTER TABLE pg_table ADD COLUMN tags varchar(255)[]');
        DB::statement('ALTER TABLE pg_table ADD COLUMN tag_ids integer[]');


        Schema::create(
            'test_table_caster',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();

                $table->jsonb('components')->nullable();
                $table->jsonb('arrays')->nullable();
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
        Schema::dropIfExists('test_table_caster');
        Schema::dropIfExists('pg_table');
        Schema::dropIfExists('test_table');
        Schema::dropIfExists('users');
    }

}
