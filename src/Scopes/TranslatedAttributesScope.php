<?php

namespace ITLeague\Microservice\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TranslatedAttributesScope implements Scope
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
        $languageTable = language(true)->getTable();
        $languageDefaultCode = language(true)->code;

        $builder->join($languageTable, $model->getTable() . '.language_id', '=', $languageTable . '.id');
        $builder->where($languageTable . '.code', app()->getLocale());

        if ($locale !== $languageDefaultCode) {
            $builder->orWhere($languageTable . '.code', $languageDefaultCode)->orderBy($languageTable . '.default', 'asc');
        }
    }
}