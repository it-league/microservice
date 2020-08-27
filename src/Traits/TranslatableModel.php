<?php


namespace ITLeague\Microservice\Traits;


use Illuminate\Database\Eloquent\Relations\HasOne;

trait TranslatableModel
{
    public function initializeTranslatableModel(): void
    {
        $this->with[] = 'translation';
    }

    public function setTranslation(array $fields): void
    {
        $languageId = $this->translation->language_id ?? language()->firstWhere('code', app()->getLocale())->id;
        $this->translation()->updateOrCreate(['language_id' => $languageId], $fields);
    }

    abstract public function translation(): HasOne;
}
