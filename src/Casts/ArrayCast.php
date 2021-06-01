<?php


namespace ITLeague\Microservice\Casts;


use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

abstract class ArrayCast implements CastsAttributes
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|null
     * @throws \Exception
     */
    final public function get($model, string $key, $value, array $attributes): ?array
    {
        $value = $value ? json_decode($value, true) : [];
        return is_array($value) ? array_filter($value) : [];
    }
}
