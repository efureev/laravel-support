<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Illuminate\Support\Facades\DB;
use Php\Support\Laravel\Tests\TestClasses\Models\SortCustomColumnModel;

class CreateSortableTableWCustomCol extends Migration
{
    use Sortable;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'sort_entities_custom_col',
            static function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                static::columnSortingPosition($table, SortCustomColumnModel::getSortingColumnName());
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
        Schema::dropIfExists('sort_entities_custom_col');
    }

}
