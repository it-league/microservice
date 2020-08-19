<?php

namespace itleague\microservice\Scopes;

use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TranslatedFieldsScope implements Scope
{
    /**
     * Получает запись из таблицы переводов на запрошенном языке (или языке по умолчанию)
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $locale = app()->getLocale();
        $repository = app(LanguageRepositoryInterface::class);
        $defaultLanguage = $repository->default();
        $builder->join($defaultLanguage->getTable(), $model->getTable() . '.language_id', '=', $defaultLanguage->getTable() . '.id');
        $builder->where($defaultLanguage->getTable() . '.code', app()->getLocale());

        if ($locale !== $defaultLanguage->code) {
            $builder->orWhere($defaultLanguage->getTable() . '.code', $defaultLanguage->code)->orderBy($defaultLanguage->getTable() . '.default', 'asc');
        }
    }
}
