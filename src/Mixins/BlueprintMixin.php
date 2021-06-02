<?php


namespace ITLeague\Microservice\Mixins;


use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;

/** @mixin Blueprint */
class BlueprintMixin
{
    public function foreignLanguageId(): Closure
    {
        return function (): ForeignKeyDefinition {
            $this->unsignedTinyInteger('language_id');
            return $this->foreign('language_id')->references('id')->on('languages')->restrictOnDelete();
        };
    }
}
