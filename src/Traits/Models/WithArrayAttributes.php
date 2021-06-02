<?php


namespace ITLeague\Microservice\Traits\Models;


use ITLeague\Microservice\Scopes\ArrayAttributeScope;


/** @mixin \Illuminate\Database\Eloquent\Model */
trait WithArrayAttributes
{
    use WithUnfilledAttributes;

    public static function bootWithArrayAttributes(): void
    {
        static::addGlobalScope(new ArrayAttributeScope());
    }
}
