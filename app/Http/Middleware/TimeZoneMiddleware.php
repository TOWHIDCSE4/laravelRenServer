<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TimeZoneMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        $timezone = ($request->hasHeader('X-Timezone')) ?
            $request->header('X-Timezone') : config('app.timezone');
        date_default_timezone_set($timezone);


        return $next($request);
    }
}
