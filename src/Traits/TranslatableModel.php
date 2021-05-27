<?php


namespace ITLeague\Microservice\Traits;


use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read \Illuminate\Database\Eloquent\Model|null $translation
 * @mixin \ITLeague\Microservice\Models\EntityModel
 */
trait TranslatableModel
{
    public static function bootTranslatableModel(): void
    {
        static::saved(
            function (self $model) {
                $model->setTranslation($model->getUnfilledAttributes());
            }
        );
    }

    public function initializeTranslatableModel(): void
    {
        $this->with[] = 'translation';
    }

    public function setTranslation(array $fields): void
    {
        if ($this->translation) {
            $this->translation->update($fields);
        } else {
            $fields['language_id'] = language()->firstWhere('code', app()->getLocale())->id;
            $this->setRelation('translation', $this->translation()->create($fields));
        }
    }

    abstract public function translation(): HasOne;
}
