<?php


namespace ITLeague\Microservice\Traits\Models;


use ITLeague\Microservice\Scopes\TranslatedAttributesScope;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait ModelForTranslatedAttributes
{
    use WithCompositePrimary;

    public static function bootModelForTranslatedAttributes(): void
    {
        static::addGlobalScope(new TranslatedAttributesScope());
    }

    public function initializeModelForTranslatedAttributes(): void
    {
        $this->timestamps = false;
        $this->incrementing = false;
        $this->guarded = [];
    }
}
