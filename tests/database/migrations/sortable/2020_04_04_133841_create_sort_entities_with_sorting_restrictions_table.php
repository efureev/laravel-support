<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Php\Support\Laravel\Traits\Database\UUID;

class CreateSortEntitiesWithSortingRestrictionsTable extends Migration
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
            'sort_entities_with_sorting_restrictions',
            static function (Blueprint $table) {
                static::columnUUID($table)->primary();
                static::columnSortingPosition($table);
                $table->string('model_type');
                $table->string('model_id');
                $table->string('title')->nullable();
                $table->softDeletes();
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
        Schema::dropIfExists('sort_entities_with_sorting_restrictions');
    }

}
