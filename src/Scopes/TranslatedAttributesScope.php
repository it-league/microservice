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
        $defaultLanguage = language(true);
        $languageTable = $defaultLanguage->getTable();

        $builder->join($languageTable, "{$model->getTable()}.language_id", '=', "$languageTable.id")
            ->where("$languageTable.code", $locale)
            ->select("{$model->getTable()}.*");

        if ($locale !== $defaultLanguage->code) {
            $builder->orWhere("$languageTable.code", $defaultLanguage->code)->orderBy("$languageTable.default");
        }
    }
}
