<?php


namespace ITLeague\Microservice\Casts\ArrayCasts;


use DB;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ITLeague\Microservice\Casts\ArrayCast;

class VarcharArrayCast extends ArrayCast implements CastsAttributes
{
    final public function set($model, string $key, $value, array $attributes)
    {
        $json = json_encode($value);
        $model->mergeUnfilled(['original_' . $key => $value]);
        return DB::raw("json_to_array('$json')::varchar[]");
    }
}
