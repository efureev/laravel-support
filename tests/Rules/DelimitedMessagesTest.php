<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Rules;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Php\Support\Laravel\Rules\Delimited;
use Php\Support\Laravel\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Regression cover for the shipped `laravelSupport::messages.delimited.*` translations.
 *
 * These assert the real published strings — no `Lang::addLines()` override — because the bug
 * being pinned was a mismatch between the placeholders the rule passes and the ones the
 * language files contain.
 */
class DelimitedMessagesTest extends AbstractTestCase
{
    private function messageFor(Delimited $rule, string $value): string
    {
        $validator = Validator::make(['attribute' => $value], ['attribute' => $rule]);

        self::assertFalse($validator->passes(), 'Expected the rule to fail so there is a message.');

        return $validator->errors()->first('attribute');
    }

    #[Test]
    public function the_min_message_substitutes_the_minimum(): void
    {
        $message = $this->messageFor((new Delimited('email'))->min(2), 'a@b.com');

        self::assertStringNotContainsString(':min', $message, 'Placeholder left unsubstituted.');
        self::assertSame('You must specify at least 2 items', $message);
    }

    #[Test]
    public function the_max_message_substitutes_the_maximum(): void
    {
        $message = $this->messageFor((new Delimited('email'))->max(1), 'a@b.com, c@d.com');

        self::assertStringNotContainsString(':max', $message, 'Placeholder left unsubstituted.');
        self::assertSame('You can only specify 1 item', $message);
    }

    #[Test]
    public function the_word_is_pluralised_from_the_boundary_not_the_actual_count(): void
    {
        // One item supplied, two required: the sentence is about the boundary, so "items".
        self::assertSame(
            'You must specify at least 2 items',
            $this->messageFor((new Delimited('email'))->min(2), 'a@b.com')
        );

        // Two items supplied, one allowed: the sentence is about the boundary, so "item".
        self::assertSame(
            'You can only specify 1 item',
            $this->messageFor((new Delimited('email'))->max(1), 'a@b.com, c@d.com')
        );
    }

    #[Test]
    public function the_message_word_can_be_customised(): void
    {
        self::assertSame(
            'You must specify at least 3 addresses',
            $this->messageFor(
                (new Delimited('email'))->min(3)->validationMessageWord('address'),
                'a@b.com'
            )
        );
    }

    #[Test]
    public function the_unique_message_is_rendered(): void
    {
        self::assertSame(
            'You may not specify duplicates.',
            $this->messageFor(new Delimited('email'), 'a@b.com, a@b.com')
        );
    }

    #[Test]
    public function the_item_length_message_substitutes_the_limit(): void
    {
        $message = $this->messageFor((new Delimited('string'))->maxItemLength(3), 'abcd');

        self::assertStringNotContainsString(':max', $message);
        self::assertSame('Each item may be at most 3 characters long', $message);
    }

    #[Test]
    public function the_russian_translations_substitute_their_placeholders_too(): void
    {
        App::setLocale('ru');

        $min = $this->messageFor((new Delimited('email'))->min(2), 'a@b.com');
        $max = $this->messageFor((new Delimited('email'))->max(1), 'a@b.com, c@d.com');
        $len = $this->messageFor((new Delimited('string'))->maxItemLength(3), 'abcd');

        self::assertStringNotContainsString(':min', $min);
        self::assertStringNotContainsString(':max', $max);
        self::assertStringNotContainsString(':max', $len);
        self::assertStringContainsString('2', $min);
        self::assertStringContainsString('1', $max);
        self::assertStringContainsString('3', $len);
    }
}
