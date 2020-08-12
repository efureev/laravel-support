<?php

use Illuminate\Database\Seeder;
use Php\Support\Laravel\Tests\TestClasses\Models\BaseModel;

class BaseTableSeeder extends Seeder
{
    /**
     * Real data
     *
     * @return void
     */
    public function run(): void
    {
        foreach (['test', 'jack', 'rogue'] as $item) {
            BaseModel::create(['name' => $item]);
        }
    }
}
