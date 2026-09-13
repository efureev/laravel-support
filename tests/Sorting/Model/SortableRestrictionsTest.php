<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Sorting\Model;

use Illuminate\Support\Facades\DB;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\Models\SortEntityWithSortingRestrictions as Entity;
use PHPUnit\Framework\Attributes\Test;

/**
 * Cover for the part of {@see \Php\Support\Laravel\Sorting\Model\Sortable} that keeps
 * independent sorting stacks inside one table: `forSortingRestrictions()` and
 * `getDefaultSortingRestrictionsSql()`.
 *
 * The fixture model and its migration existed but no test ever loaded them.
 */
class SortableRestrictionsTest extends AbstractTestCase
{
    private const MIGRATION = 'sortable/2020_04_04_133841_create_sort_entities_with_sorting_restrictions_table.php';

    /** @var string[] */
    protected array $migrations = [self::MIGRATION];

    private function make(string $ownerId, string $title, ?int $position = null): Entity
    {
        $entity = new Entity(['title' => $title, 'model_type' => 'Owner', 'model_id' => $ownerId]);

        if ($position !== null) {
            $entity->setSortingPosition($position);
        }

        $entity->save();

        return $entity->refresh();
    }

    #[Test]
    public function each_stack_numbers_itself_from_one(): void
    {
        $a1 = $this->make('a', 'a1');
        $a2 = $this->make('a', 'a2');
        $b1 = $this->make('b', 'b1');
        $b2 = $this->make('b', 'b2');

        self::assertSame(1, $a1->sortingPosition());
        self::assertSame(2, $a2->sortingPosition());

        // Without the restriction these would continue at 3 and 4.
        self::assertSame(1, $b1->sortingPosition());
        self::assertSame(2, $b2->sortingPosition());
    }

    #[Test]
    public function reordering_one_stack_leaves_the_other_untouched(): void
    {
        $a1 = $this->make('a', 'a1');
        $a2 = $this->make('a', 'a2');
        $a3 = $this->make('a', 'a3');

        $b1 = $this->make('b', 'b1');
        $b2 = $this->make('b', 'b2');

        $a3->setSortingPosition(1)->save();

        self::assertSame(1, $a3->refresh()->sortingPosition());
        self::assertSame(2, $a1->refresh()->sortingPosition());
        self::assertSame(3, $a2->refresh()->sortingPosition());

        self::assertSame(1, $b1->refresh()->sortingPosition());
        self::assertSame(2, $b2->refresh()->sortingPosition());
    }

    #[Test]
    public function inserting_into_the_middle_of_a_stack_shifts_only_that_stack(): void
    {
        $this->make('a', 'a1');
        $this->make('a', 'a2');
        $b1 = $this->make('b', 'b1');

        $inserted = $this->make('a', 'a-mid', 1);

        self::assertSame(1, $inserted->sortingPosition());
        self::assertSame(
            [
                1,
                2,
                3,
            ],
            Entity::query()
                ->where('model_id', 'a')
                ->orderBy('sorting_position')
                ->pluck('sorting_position')
                ->all()
        );
        self::assertSame(1, $b1->refresh()->sortingPosition());
    }

    #[Test]
    public function soft_deleted_rows_are_excluded_from_the_stack(): void
    {
        $this->make('a', 'a1');
        $this->make('a', 'a2');

        // The restriction SQL filters on `deleted_at IS NULL`; a soft-deleted row must not
        // take part in the max() that decides the next position.
        DB::table('sort_entities_with_sorting_restrictions')
            ->where('title', 'a2')
            ->update(['deleted_at' => now()]);

        $next = $this->make('a', 'a3');

        self::assertSame(2, $next->sortingPosition());
    }
}
