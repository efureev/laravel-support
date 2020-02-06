<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Php\Support\Laravel\Traits\Database\UUID;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Php\Support\Laravel\Tests\Models\SortEntity;

class CreateSortableTable extends Migration
{
    use Sortable, UUID;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'sort_entities',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();
                static::columnSortingPosition($table, (new SortEntity())->getSortingPositionColumn());
                $table->string('title')->nullable();
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
        Schema::dropIfExists('sort_entities');
    }

}
