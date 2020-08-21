<?php

use Illuminate\Database\Eloquent\Collection;
use itleague\microservice\Models\Language;
use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;

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
        if ($default === false) {
            return app(LanguageRepositoryInterface::class)->all();
        }

        return app(LanguageRepositoryInterface::class)->default();
    }
}
