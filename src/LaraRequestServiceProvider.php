<?php

namespace Illuminate\Foundation\Providers;

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

                $request
                    ->setContainer($app)
                    ->setRedirector($app->make(Redirector::class));
            }
        );
    }
}
