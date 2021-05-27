<?php


namespace ITLeague\Microservice\Scopes;


use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MultipleFileAttributeScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        /** @var \ITLeague\Microservice\Traits\Models\WithFileAttributes $model */
        foreach ($model->getFileAttributesSettings() as $attribute => $settings) {
            if ($model->isFileAttributeMultiple($attribute)) {
                $builder->addSelect(DB::raw("array_to_json(\"{$model->getTable()}\".\"{$attribute}\") as \"$attribute\""));
            }
        }
    }
}
