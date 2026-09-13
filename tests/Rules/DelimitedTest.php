<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Rules;

use Illuminate\Support\Facades\Validator;
use Php\Support\Laravel\Rules\Delimited;
use Php\Support\Laravel\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class DelimitedTest extends AbstractTestCase
{
    /**
     * @var Delimited
     */
    private $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = (new Delimited('email'));
    }

    #[Test]
    public function it_can_validate_comma_separated_email_addresses(): void
    {
        $this->assertRulePasses('sebastian@example.com, alex@example.com');
        $this->assertRulePasses('');
        $this->assertRulePasses('sebastian@example.com');
        $this->assertRulePasses('sebastian@example.com, alex@example.com, brent@example.com');
        $this->assertRulePasses(' sebastian@example.com   , alex@example.com  ,   brent@example.com  ');

        $this->assertRuleFails('invalid@');
        $this->assertRuleFails('nocomma@example.com nocommatoo@example.com');

        $this->assertRuleFails('valid@example.com, invalid@');
    }

    #[Test]
    public function it_can_validate_a_minimum_of_valid_addresses(): void
    {
        $this->rule->min(1);
        $this->assertRuleFails('');
        $this->assertRuleFails('  ');
        $this->assertRulePasses('sebastian@example.com');

        $this->rule->min(2);
        $this->assertRuleFails('');
        $this->assertRuleFails('sebastian@example.com');
        $this->assertRulePasses('sebastian@example.com, alex@example.com');
        $this->assertRulePasses('sebastian@example.com, alex@example.com, brent@example.com');
    }

    #[Test]
    public function it_can_validate_a_maximum_amount_of_emailaddress(): void
    {
        $this->rule->max(2);
        $this->assertRulePasses('');
        $this->assertRulePasses('sebastian@example.com');
        $this->assertRulePasses('sebastian@example.com, alex@example.com');
        $this->assertRuleFails('sebastian@example.com, alex@example.com, brent@example.com');
    }

    #[Test]
    public function it_will_fail_if_not_all_email_addresses_are_unique(): void
    {
        $this->assertRuleFails('sebastian@example.com, sebastian@example.com');
    }

    #[Test]
    public function it_can_allow_duplicates(): void
    {
        $this->rule->allowDuplicates();

        $this->assertRulePasses('sebastian@example.com, sebastian@example.com');
    }

    #[Test]
    public function it_can_use_a_custom_separator(): void
    {
        $this->rule->separatedBy(';');

        $this->assertRulePasses('sebastian@example.com; freek@example.com');
        $this->assertRuleFails('sebastian@example.com, freek@example.com');
    }

    #[Test]
    public function it_can_skip_trimming_items(): void
    {
        $this->rule->doNotTrimItems();

        $this->assertRulePasses('sebastian@example.com,freek@example.com');
        $this->assertRuleFails('sebastian@example.com, freek@example.com');
        $this->assertRuleFails('sebastian@example.com , freek@example.com');
    }

    #[Test]
    public function it_can_accept_a_rule_as_an_array(): void
    {
        $rule = new Delimited(['email']);

        $value = 'sebastian@example.com, alex@example.com, brent@example.com';

        $this->assertTrue(Validator::make(['attribute' => $value], ['attribute' => $rule])->passes());
        $this->assertFalse(Validator::make(['attribute' => 'blablabla'], ['attribute' => $rule])->passes());
    }

    #[Test]
    public function it_can_use_composite_rules(): void
    {
        $rule = new Delimited('email|max:20');

        $this->assertTrue(Validator::make(['attribute' => 'short@example.com'], ['attribute' => $rule])->passes());
        $this->assertFalse(Validator::make(['attribute' => 'short'], ['attribute' => $rule])->passes());
        $long = 'loooooooonnnggg@example.com';

        $this->assertFalse(Validator::make(['attribute' => $long], ['attribute' => $rule])->passes());
    }


    #[Test]
    public function it_can_handle_numeric_values_properly(): void
    {
        $rule = new Delimited('numeric');
        $this->assertTrue(Validator::make(['attribute' => '0, 1'], ['attribute' => $rule->min(2)])->passes());
    }

    #[Test]
    public function blank_items_do_not_count_towards_the_minimum(): void
    {
        // 'a@b.com, ,c@d.com' holds two addresses, not three: the middle element is whitespace.
        $this->rule->min(3);
        $this->assertRuleFails('a@b.com, ,c@d.com');

        $this->rule->min(2);
        $this->assertRulePasses('a@b.com, ,c@d.com');
    }

    #[Test]
    public function blank_items_do_not_count_towards_the_maximum(): void
    {
        $this->rule->max(2);
        $this->assertRulePasses('a@b.com, ,c@d.com');
        $this->assertRulePasses('a@b.com,,,c@d.com');
    }

    #[Test]
    public function blank_items_are_not_validated_as_values(): void
    {
        // A blank element must be dropped, not handed to the underlying rule.
        $this->assertRulePasses('a@b.com,   ,c@d.com');
    }

    #[Test]
    public function surrounding_whitespace_is_trimmed_from_the_whole_value(): void
    {
        $this->assertRulePasses('   a@b.com , c@d.com   ');
    }

    #[Test]
    public function it_can_limit_the_length_of_a_single_item(): void
    {
        $this->rule->maxItemLength(20);

        $this->assertRulePasses('short@example.com');
        $this->assertRuleFails('loooooooooooooooong@example.com');
    }

    #[Test]
    public function the_item_length_limit_counts_characters_not_bytes(): void
    {
        $rule = (new Delimited('string'))->maxItemLength(5);

        // Five Cyrillic characters are ten bytes.
        $this->assertTrue(Validator::make(['attribute' => 'ключи'], ['attribute' => $rule])->passes());
        $this->assertFalse(Validator::make(['attribute' => 'ключики'], ['attribute' => $rule])->passes());
    }

    #[Test]
    public function a_non_positive_item_length_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->rule->maxItemLength(0);
    }

    #[Test]
    public function an_empty_separator_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The separator must not be empty.');

        $this->rule->separatedBy('');
    }

    #[Test]
    public function the_fluent_setters_return_the_rule(): void
    {
        self::assertSame($this->rule, $this->rule->min(1));
        self::assertSame($this->rule, $this->rule->max(9));
        self::assertSame($this->rule, $this->rule->allowDuplicates());
        self::assertSame($this->rule, $this->rule->separatedBy(';'));
        self::assertSame($this->rule, $this->rule->doNotTrimItems());
        self::assertSame($this->rule, $this->rule->validationMessageWord('thing'));
        self::assertSame($this->rule, $this->rule->maxItemLength(5));
    }

    protected function assertRulePasses(mixed $value): void
    {
        $this->assertTrue($this->rulePasses($value));
    }

    protected function assertRuleFails(mixed $value): void
    {
        $this->assertFalse($this->rulePasses($value));
    }

    public function rulePasses(mixed $value): bool
    {
        return Validator::make(['attribute' => $value], ['attribute' => $this->rule])->passes();
    }
}
