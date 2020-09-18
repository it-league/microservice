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
        if ($default === false) {
            return app(LanguageRepositoryInterface::class)->all();
        }

        return app(LanguageRepositoryInterface::class)->default();
    }
}

if (! function_exists('file_url')) {
    /**
     * Return url for file storage
     *
     * @param string $fileId
     * @param bool $inline
     *
     * @return string
     */
    function file_url(string $fileId, bool $inline = true)
    {
        $url = config('microservice.api_uri') . '/' . config('microservice.storage_prefix') . '/file/' . $fileId;
        if ($inline !== true) {
            $url .= '?disposition=' . HeaderUtils::DISPOSITION_ATTACHMENT;
        }
        return $url;
    }
}
