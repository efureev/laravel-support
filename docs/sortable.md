# Sortable

Helps you make sort for your models. May works with `D'n'D`

## Legend
- if a new record is added with following Sorting Position:
    - equal `null`
    - equal `0`
    - less than `0`

it's Sorting Position (next - SP) will be `max + 1`  from (existing records)
- if a new record is added with an already existing SP, then all records after it will be increased by `1`
- if a new record is added with a lager SP than existing ones, then all records will remain in their SP
- if a existed record is moved with a lager or less SP than it had, then `max SP of records` doesn't change, and some records in the record`s stack will be change in their SP

### Example

Add code in a migration:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Database\Sortable;
use Php\Support\Laravel\Tests\TestClasses\Models\SortEntity;

class CreateSortableTable extends Migration
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
            'sort_entities',
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('title')->nullable();
                static::columnSortingPosition($table, SortEntity::getSortingColumnName());
            }
        );
    }
}
```

Add the trait `Sortable` in a model:
```php
<?php

use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Sorting\Model\Sortable;

class SortEntity extends Model
{
    use Sortable;
}
```

