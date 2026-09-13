<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Functional;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Php\Support\Laravel\Tests\TestClasses\Models\PgArrayModel;
use PHPUnit\Framework\Attributes\Test;
use InvalidArgumentException;
use RuntimeException;

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

        // Laravel's schema builder has no native-array column type, so swap the jsonb column
        // for a real text[] one: the @> / && / ANY / ALL operators below need a true array.
        DB::statement('ALTER TABLE pg_table DROP COLUMN tags');
        DB::statement('ALTER TABLE pg_table ADD COLUMN tags text[]');
    }

    protected function tearDown(): void
    {
        $this->getConnection()->getSchemaBuilder()->dropIfExists('pg_table');
        parent::tearDown();
    }

    #[Test]
    public function it_can_filter_by_pg_array_contains(): void
    {
        PgArrayModel::create(['tags' => ['php', 'laravel', 'support']]);
        PgArrayModel::create(['tags' => ['php', 'react']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayContains('tags', ['php', 'laravel'])->get());
        $this->assertCount(2, PgArrayModel::wherePgArrayContains('tags', ['php'])->get());
    }

    #[Test]
    public function it_can_filter_by_pg_array_contains_any(): void
    {
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayContainsAny('tags', 'php')->get());
    }

    #[Test]
    public function it_can_filter_by_pg_array_contains_only(): void
    {
        PgArrayModel::create(['tags' => ['php', 'php']]);
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayContainsOnly('tags', 'php')->get());
    }

    #[Test]
    public function a_closure_may_supply_a_raw_array_literal(): void
    {
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(
            1,
            PgArrayModel::wherePgArrayContains('tags', static fn(): string => '{php}')->get()
        );
    }

    #[Test]
    public function a_closure_that_does_not_return_a_string_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Result`s value must have STRING type!');

        PgArrayModel::wherePgArrayContains('tags', static fn(): array => ['php'])->get();
    }

    #[Test]
    public function contains_any_rejects_a_set_where_it_expects_one_element(): void
    {
        // `? = ANY (col)` compares a single element; passing an array used to reach the driver
        // and fail there with an unrelated message.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('wherePgArrayOverlapWith');

        PgArrayModel::wherePgArrayContainsAny('tags', ['php', 'laravel'])->get();
    }

    #[Test]
    public function contains_only_rejects_a_set_where_it_expects_one_element(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('wherePgArrayOverlapWith');

        PgArrayModel::wherePgArrayContainsOnly('tags', ['php'])->get();
    }

    #[Test]
    public function documented_pitfall_contains_only_matches_empty_arrays(): void
    {
        // `= ALL (col)` is true for a zero-length array. Documented, not a bug.
        PgArrayModel::create(['tags' => []]);
        PgArrayModel::create(['tags' => ['php']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(2, PgArrayModel::wherePgArrayContainsOnly('tags', 'php')->get());
    }

    #[Test]
    public function the_cast_round_trips_through_a_real_array_column(): void
    {
        $model = PgArrayModel::create(['tags' => ['php', 'laravel', 'ключ']]);

        $this->assertSame(['php', 'laravel', 'ключ'], $model->fresh()->tags);
    }

    #[Test]
    public function a_null_array_column_stays_null(): void
    {
        $model = PgArrayModel::create(['title' => 'no tags']);

        $this->assertNull($model->fresh()?->getAttribute('tags'));
    }

    #[Test]
    public function the_scope_can_be_composed_inside_another_scope(): void
    {
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript']]);

        $this->assertCount(1, PgArrayModel::byTag('php')->get());
    }

    #[Test]
    public function the_column_name_is_quoted_as_an_identifier(): void
    {
        PgArrayModel::create(['tags' => ['php']]);

        $sql = PgArrayModel::wherePgArrayContains('tags', ['php'])->toSql();

        self::assertStringContainsString('"tags" @> ?', $sql, 'The identifier must be quoted.');
    }

    #[Test]
    public function a_column_name_that_is_not_a_bare_identifier_is_rejected(): void
    {
        // The column cannot be bound as a parameter, so it has to be validated: interpolating
        // it raw let a caller close the expression and append arbitrary SQL.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a valid column name');

        PgArrayModel::wherePgArrayContains('tags) OR (1=1', ['php'])->get();
    }

    #[Test]
    public function every_raw_scope_validates_its_column_name(): void
    {
        $injection = 'tags) OR (1=1';

        foreach (
            [
                static fn() => PgArrayModel::wherePgArrayContainsAny($injection, 'php')->get(),
                static fn() => PgArrayModel::wherePgArrayContainsOnly($injection, 'php')->get(),
                static fn() => PgArrayModel::wherePgArrayOverlapWith($injection, ['php'])->get(),
            ] as $i => $call
        ) {
            try {
                $call();
                self::fail("Scope #$i accepted an injected column name.");
            } catch (InvalidArgumentException $e) {
                self::assertStringContainsString('is not a valid column name', $e->getMessage());
            }
        }
    }

    #[Test]
    public function a_table_qualified_column_is_accepted(): void
    {
        PgArrayModel::create(['tags' => ['php']]);

        self::assertCount(1, PgArrayModel::wherePgArrayContains('pg_table.tags', ['php'])->get());
    }

    #[Test]
    public function it_appends_to_an_array_column(): void
    {
        $model = PgArrayModel::create(['tags' => ['php']]);

        $updated = PgArrayModel::query()->whereKey($model->getKey())->pgArrayAppend('tags', ['laravel']);

        self::assertSame(1, $updated);
        self::assertSame(['php', 'laravel'], $model->fresh()?->tags);
    }

    #[Test]
    public function appending_keeps_duplicates_unless_asked_otherwise(): void
    {
        $model = PgArrayModel::create(['tags' => ['php']]);

        PgArrayModel::query()->whereKey($model->getKey())->pgArrayAppend('tags', ['php']);
        self::assertSame(['php', 'php'], $model->fresh()?->tags);

        PgArrayModel::query()->whereKey($model->getKey())->pgArrayAppend('tags', ['php'], unique: true);
        self::assertSame(['php'], $model->fresh()->tags);
    }

    #[Test]
    public function appending_starts_a_null_column_off(): void
    {
        $model = PgArrayModel::create(['title' => 'no tags']);
        self::assertNull($model->fresh()?->tags);

        PgArrayModel::query()->whereKey($model->getKey())->pgArrayAppend('tags', ['php']);

        self::assertSame(['php'], $model->fresh()?->tags);
    }

    #[Test]
    public function appending_touches_every_matching_row(): void
    {
        PgArrayModel::create(['tags' => ['php'], 'title' => 'a']);
        PgArrayModel::create(['tags' => ['php'], 'title' => 'b']);
        $untouched = PgArrayModel::create(['tags' => ['ruby'], 'title' => 'c']);

        $updated = PgArrayModel::wherePgArrayContains('tags', ['php'])->pgArrayAppend('tags', ['web']);

        self::assertSame(2, $updated);
        self::assertSame(['ruby'], $untouched->fresh()?->tags);
    }

    #[Test]
    public function it_removes_every_occurrence_of_a_value(): void
    {
        $model = PgArrayModel::create(['tags' => ['php', 'laravel', 'php']]);

        $updated = PgArrayModel::query()->whereKey($model->getKey())->pgArrayRemove('tags', ['php']);

        self::assertSame(1, $updated);
        self::assertSame(['laravel'], $model->fresh()?->tags);
    }

    #[Test]
    public function it_removes_several_values_at_once(): void
    {
        $model = PgArrayModel::create(['tags' => ['php', 'laravel', 'web']]);

        PgArrayModel::query()->whereKey($model->getKey())->pgArrayRemove('tags', ['php', 'web']);

        self::assertSame(['laravel'], $model->fresh()?->tags);
    }

    #[Test]
    public function the_write_scopes_are_no_ops_for_an_empty_value_set(): void
    {
        $model = PgArrayModel::create(['tags' => ['php']]);

        self::assertSame(0, PgArrayModel::query()->whereKey($model->getKey())->pgArrayAppend('tags', []));
        self::assertSame(0, PgArrayModel::query()->whereKey($model->getKey())->pgArrayRemove('tags', []));
        self::assertSame(['php'], $model->fresh()?->tags);
    }

    #[Test]
    public function the_write_scopes_quote_the_literal_rather_than_interpolating_it(): void
    {
        // A value carrying a quote must land in the column verbatim, not break the statement.
        $model = PgArrayModel::create(['tags' => ['safe']]);

        PgArrayModel::query()->whereKey($model->getKey())->pgArrayAppend('tags', ["o'brien"]);

        self::assertSame(['safe', "o'brien"], $model->fresh()?->tags);

        PgArrayModel::query()->whereKey($model->getKey())->pgArrayRemove('tags', ["o'brien"]);

        self::assertSame(['safe'], $model->fresh()->tags);
    }

    #[Test]
    public function the_write_scopes_validate_the_column_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PgArrayModel::query()->pgArrayAppend('tags) OR (1=1', ['php']);
    }

    #[Test]
    public function it_can_filter_by_pg_array_overlap(): void
    {
        PgArrayModel::create(['tags' => ['php', 'laravel']]);
        PgArrayModel::create(['tags' => ['javascript', 'react']]);

        $this->assertCount(1, PgArrayModel::wherePgArrayOverlapWith('tags', ['php', 'python'])->get());
    }
}
