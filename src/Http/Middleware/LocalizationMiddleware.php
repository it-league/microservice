<?php

namespace ITLeague\Microservice\Http\Middleware;

use Closure;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $local = $request->getPreferredLanguage(language()->pluck('code')->toArray());

        app()->setLocale($local);
        return $next($request);
    }
}
