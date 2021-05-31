<?php

namespace ITLeague\Microservice\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $available = language()->pluck('code')->toArray();
        } catch (QueryException) {
            $available = null;
        }

        $locale = $request->getPreferredLanguage($available);
        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        return $response;
    }
}
