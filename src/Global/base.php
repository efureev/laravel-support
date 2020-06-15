<?php

if (!function_exists('user')) {
    /**
     * Returns the current user authenticated, or `null`
     *
     * @param string|null $guard
     *
     * @return null|\Illuminate\Contracts\Auth\Authenticatable
     */
    function user($guard = null)
    {
        return app('auth')->guard($guard)->user();
    }
}
