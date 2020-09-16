<?php

declare(strict_types=1);

namespace Php\Support\Laravel;

use Illuminate\Routing\Redirector;
use Illuminate\Support\ServiceProvider;
use Php\Support\Laravel\Http\LaraRequest;

class LaraRequestServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(
            LaraRequest::class,
            static function ($request, $app) {
                $request = LaraRequest::createFrom($app['request'], $request);

                $request->setContainer($app);
            }
        );
    }
}
