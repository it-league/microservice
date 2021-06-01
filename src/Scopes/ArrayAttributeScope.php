<?php


namespace ITLeague\Microservice\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ITLeague\Microservice\Casts\ArrayCast;

class ArrayAttributeScope implements \Illuminate\Database\Eloquent\Scope
{
    public function apply(Builder $builder, Model $model)
    {
        foreach ($model->getCasts() as $attribute => $cast) {
            if (is_subclass_of($cast, ArrayCast::class)) {
                if (is_null($builder->getQuery()->columns)) {
                    $builder->addSelect($model->getTable() . '.*');
                }
                $builder->addSelect(DB::raw("array_to_json(\"{$model->getTable()}\".\"$attribute\") as \"$attribute\""));
            }
        }
    }
}
