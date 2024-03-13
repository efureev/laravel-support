<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Rules;

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
    public function it_can_validate_comma_separated_email_addresses()
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
    public function it_can_validate_a_minimum_of_valid_addresses()
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
    public function it_can_validate_a_maximum_amount_of_emailaddress()
    {
        $this->rule->max(2);
        $this->assertRulePasses('');
        $this->assertRulePasses('sebastian@example.com');
        $this->assertRulePasses('sebastian@example.com, alex@example.com');
        $this->assertRuleFails('sebastian@example.com, alex@example.com, brent@example.com');
    }

    #[Test]
    public function it_will_fail_if_not_all_email_addresses_are_unique()
    {
        $this->assertRuleFails('sebastian@example.com, sebastian@example.com');
    }

    #[Test]
    public function it_can_allow_duplicates()
    {
        $this->rule->allowDuplicates();

        $this->assertRulePasses('sebastian@example.com, sebastian@example.com');
    }

    #[Test]
    public function it_can_use_a_custom_separator()
    {
        $this->rule->separatedBy(';');

        $this->assertRulePasses('sebastian@example.com; freek@example.com');
        $this->assertRuleFails('sebastian@example.com, freek@example.com');
    }

    #[Test]
    public function it_can_skip_trimming_items()
    {
        $this->rule->doNotTrimItems();

        $this->assertRulePasses('sebastian@example.com,freek@example.com');
        $this->assertRuleFails('sebastian@example.com, freek@example.com');
        $this->assertRuleFails('sebastian@example.com , freek@example.com');
    }

    #[Test]
    public function it_can_accept_a_rule_as_an_array()
    {
        $rule = new Delimited(['email']);

        $this->assertTrue($rule->passes('attribute', 'sebastian@example.com, alex@example.com, brent@example.com'));
        $this->assertFalse($rule->passes('attribute', 'blablabla'));
    }

    #[Test]
    public function it_can_use_composite_rules()
    {
        $rule = new Delimited('email|max:20');

        $this->assertTrue($rule->passes('attribute', 'short@example.com'));
        $this->assertFalse($rule->passes('attribute', 'short'));
        $this->assertFalse($rule->passes('attribute', 'loooooooonnnggg@example.com'));
    }


    #[Test]
    public function it_can_handle_numeric_values_properly()
    {
        $rule = new Delimited('numeric');
        $this->assertTrue($rule->min(2)->passes('attribute', '0, 1'));
    }

    protected function assertRulePasses(string $value)
    {
        $this->assertTrue($this->rulePasses($value));
    }

    protected function assertRuleFails(string $value)
    {
        $this->assertFalse($this->rulePasses($value));
    }

    public function rulePasses(string $value): bool
    {
        return $this->rule->passes('attribute', $value);
    }
}
