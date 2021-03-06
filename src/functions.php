<?php

use Illuminate\Database\Eloquent\Collection;
use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;

if (! function_exists('language')) {
    /**
     * Get collection with available languages or default language
     */
    function language(bool $default = false): Language|Collection|array
    {
        $repository = app(LanguageRepositoryInterface::class);

        if ($default === false) {
            return $repository->all();
        }

        return $repository->default();
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
    function file_url(string $fileId, bool $inline = true): string
    {
        $url = config('microservice.api_uri') . '/api/' . config('microservice.storage.prefix') . '/file/' . $fileId;
        if ($inline !== true) {
            $url .= '?disposition=' . HeaderUtils::DISPOSITION_ATTACHMENT;
        }
        return $url;
    }
}
