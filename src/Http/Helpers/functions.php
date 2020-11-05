<?php

use Illuminate\Database\Eloquent\Collection;
use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;

if (! function_exists('language')) {
    /**
     * Get collection with available languages or default language
     *
     * @param bool $default
     *
     * @return Language[]|Language|Collection
     */
    function language(bool $default = false)
    {
        $repository = app(LanguageRepositoryInterface::class);

        if ($default === false) {
            return $repository->all();
        }

        return $repository->default();
    }
}
