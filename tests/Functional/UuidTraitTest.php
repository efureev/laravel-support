<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Illuminate\Support\Facades\Schema;

class UuidTraitTest extends AbstractFunctionalTestCase
{
    public function testMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations_uuid');

        static::assertTrue(Schema::hasTable('uuid_table'));
        static::assertTrue(Schema::hasColumn('uuid_table', 'id'));
        static::assertTrue(Schema::hasColumn('uuid_table', 'title'));

        static::assertTrue(Schema::hasTable('uuid_table2'));
        static::assertTrue(Schema::hasColumn('uuid_table2', 'id'));
        static::assertTrue(Schema::hasColumn('uuid_table2', 'table_id'));

        static::assertTrue(Schema::hasTable('uuid_table3'));
        static::assertTrue(Schema::hasColumn('uuid_table3', 'id'));
        static::assertTrue(Schema::hasColumn('uuid_table3', 'table_id'));
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
