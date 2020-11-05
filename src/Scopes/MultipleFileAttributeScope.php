<?php


namespace ITLeague\Microservice\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MultipleFileAttributeScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        /** @var \ITLeague\Microservice\Traits\WithFileAttributes $model */
        foreach ($model->getFileAttributesSettings() as $attribute => $settings) {
            if ($model->isFileAttributeMultiple($attribute)) {
                $builder->select()->selectRaw("array_to_json(\"$attribute\") as \"$attribute\"");
            }
        }
    }
}
