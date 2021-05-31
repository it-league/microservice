<?php


namespace ITLeague\Microservice\Mixins;


use Closure;
use Illuminate\Database\Schema\Blueprint;

/** @mixin Blueprint */
class BlueprintMixin
{
    public function foreignLanguageId(): Closure
    {
        return function (): void {
            $this->unsignedTinyInteger('language_id');
            $this->foreign('language_id')->references('id')->on('languages')->onDelete('restrict')->touchParent();
        };
    }
}
