<?php

namespace ITLeague\Microservice\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;

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
        try {
            $available = language()->pluck('code')->toArray();
        } catch (QueryException $e) {
            $available = null;
        }

        $locale = $request->getPreferredLanguage($available);
        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        return $response;
    }
}
