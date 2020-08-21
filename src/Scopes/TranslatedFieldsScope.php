<?php

namespace itleague\microservice\Scopes;

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
        $builder->join(language(true)->getTable(), $model->getTable() . '.language_id', '=', language(true)->getTable() . '.id');
        $builder->where(language(true)->getTable() . '.code', app()->getLocale());

        if ($locale !== language(true)->code) {
            $builder->orWhere(language(true)->getTable() . '.code', language(true)->code)->orderBy(language(true)->getTable() . '.default', 'asc');
        }
    }
}
