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
        $locale = $request->getPreferredLanguage();

        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        return $response;
    }
}
