<?php

namespace itleague\microservice\Http\Middleware;

use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
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
        $repository = app(LanguageRepositoryInterface::class);
        $local = $request->hasHeader('x-localization') ? $request->header('x-localization') : $repository->default()->code;
        app()->setLocale($local);
        return $next($request);
    }
}
