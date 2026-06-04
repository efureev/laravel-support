<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Php\Support\Laravel\Tests\TestClasses\Models\PgArrayModel;
use PHPUnit\Framework\Attributes\Test;

class PostgresArrayTest extends AbstractFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only test');
        }

        $this->getConnection()->getSchemaBuilder()->create(
            'pg_table',
            function (Blueprint $table) {
                $table->id();
                $table->jsonb('tags')->nullable();
                $table->jsonb('tag_ids')->nullable();
                $table->string('title')->nullable();
                $table->timestamps();
            }
        );
        
        // Postgres actually uses specialized array types, but Laravel's jsonb can sometimes be used or we can use raw.
        // The trait uses @> operator which is for arrays or jsonb.
        // Let's use raw to create a real array column for the test if possible.
        DB::statement('ALTER TABLE pg_table DROP COLUMN tags');
        DB::statement('ALTER TABLE pg_table ADD COLUMN tags text[]');
    }

    protected function tearDown(): void
    {
        $this->getConnection()->getSchemaBuilder()->dropIfExists('pg_table');
        parent::tearDown();
    }

    #[Test]
    public function it_can_filter_by_pg_array_contains()
    {
        PgArrayModel::create(['tags' => ['php', 'laravel', 'support']]);
        PgArrayModel::create(['tags' => ['php', 'react']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayContains('tags', ['php', 'laravel'])->get());
        $this->assertCount(2, PgArrayModel::wherePgArrayContains('tags', ['php'])->get());
    }

    #[Test]
    public function it_can_filter_by_pg_array_contains_any()
    {
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayContainsAny('tags', 'php')->get());
    }

    #[Test]
    public function it_can_filter_by_pg_array_contains_only()
    {
        PgArrayModel::create(['tags' => ['php', 'php']]);
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayContainsOnly('tags', 'php')->get());
    }

    #[Test]
    public function it_can_filter_by_pg_array_overlap()
    {
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript', 'react']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayOverlapWith('tags', ['php', 'python'])->get());
    }
}
