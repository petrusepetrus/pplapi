<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        /*
         * AGP - redirect to the SPA login page rather than the default
         * laravel login route
         */
        //if (! $request->expectsJson()) {
        //    return route('login');
        //}
        if (! $request->expectsJson()) {
            return url(env('SPA_URL') . '/login');
        }
    }
}
