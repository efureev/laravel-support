<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Php\Support\Laravel\ServiceProvider;
use Php\Support\Laravel\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class TranslationPublishingTest extends AbstractTestCase
{
    #[Test]
    public function the_package_translations_are_loaded(): void
    {
        self::assertSame(
            'You may not specify duplicates.',
            Lang::get('laravelSupport::messages.delimited.unique', [], 'en')
        );

        self::assertSame(
            'Вы не можете указывать дубликаты.',
            Lang::get('laravelSupport::messages.delimited.unique', [], 'ru')
        );
    }

    #[Test]
    public function translations_publish_into_the_applications_lang_path(): void
    {
        // `resource_path('lang/...')` was the pre-Laravel-9 location. Since Laravel 9 the lang
        // directory resolves to base_path('lang') unless resources/lang exists, so a hardcoded
        // resource_path() target publishes into a directory the app never reads.
        $paths = BaseServiceProvider::pathsToPublish(ServiceProvider::class);

        self::assertNotEmpty($paths, 'The provider publishes nothing.');

        $target = reset($paths);

        self::assertSame($this->app->langPath('vendor/laravelSupport'), $target);
        self::assertStringContainsString($this->app->langPath(), (string)$target);
    }

    #[Test]
    public function translations_are_published_under_a_named_tag(): void
    {
        $paths = BaseServiceProvider::pathsToPublish(ServiceProvider::class, 'laravel-support-lang');

        self::assertNotEmpty(
            $paths,
            'Without a tag the translations cannot be published selectively.'
        );
        self::assertSame($this->app->langPath('vendor/laravelSupport'), reset($paths));
    }

    #[Test]
    public function the_published_source_directory_exists(): void
    {
        $paths = BaseServiceProvider::pathsToPublish(ServiceProvider::class, 'laravel-support-lang');

        $source = (string)array_key_first($paths);

        self::assertDirectoryExists($source);
        self::assertFileExists($source . '/en/messages.php');
        self::assertFileExists($source . '/ru/messages.php');
    }
}
