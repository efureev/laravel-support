<?php

use Illuminate\Support\Facades\Route;

Route::prefix('back-api')
    ->name('back.')
    ->group(
        static function () {
            Route::get('test', static fn(): string => 'back')->name('test');
        }
    );
