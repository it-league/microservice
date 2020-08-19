<?php


namespace itleague\microservice\Traits;


use itleague\microservice\Models\Language;
use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

trait TranslatableModel
{
    public function initializeTranslatableModel(): void
    {
        $this->with[] = 'translation';
    }

    public function setTranslation(array $fields): void
    {
        $languageId = $this->translation->language_id ?? app(LanguageRepositoryInterface::class)->all()->firstWhere('code', app()->getLocale())->id;
        $this->translation()->updateOrCreate(['language_id' => $languageId], $fields);
    }

    abstract public function translation(): HasOne;
}
