<?php

declare(strict_types=1);

namespace Php\Support\Laravel;

use Illuminate\Support\ServiceProvider as SP;

class ServiceProvider extends SP
{
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/laravelSupport'),
            ]
        );

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang/', 'laravelSupport');
    }
}
