<?php

declare(strict_types=1);

namespace Php\Support\Laravel;

use Illuminate\Support\ServiceProvider as SP;

/**
 * Registers the package's own translations (`laravelSupport` namespace).
 *
 * Auto-discovered — no manual registration needed. To override the messages:
 *
 * ```bash
 * php artisan vendor:publish --tag=laravel-support-lang
 * ```
 */
class ServiceProvider extends SP
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'laravelSupport');

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [
                // `langPath()` resolves to resources/lang when that directory exists and to
                // base_path('lang') otherwise — which is the default in Laravel 9+.
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/laravelSupport'),
            ],
            'laravel-support-lang'
        );
    }
}
