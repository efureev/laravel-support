<?php

use Illuminate\Support\Facades\Route;

Route::prefix('front-api')
    ->name('front.')
    ->group(
        static function () {
            Route::get('test', static fn(): string => 'front')->name('test');
        }
    );
