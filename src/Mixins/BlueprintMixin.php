<?php


namespace ITLeague\Microservice\Mixins;


use Illuminate\Database\Schema\Blueprint;

/** @mixin Blueprint */
class BlueprintMixin
{
    public function softDeletesWithUserFields()
    {
        return function () {
            $this->softDeletes();
            $this->uuid('deleted_by')->nullable();
        };
    }

    public function timestampsWithUserFields()
    {
        return function () {
            $this->timestamp('created_at')->useCurrent();
            $this->timestamp('updated_at')->useCurrent();
            $this->uuid('created_by');
            $this->uuid('updated_by');
        };
    }

    public function foreignLanguageId()
    {
        return function () {
            $this->unsignedTinyInteger('language_id');
            $this->foreign('language_id')->references('id')->on('languages')->onDelete('restrict');
        };
    }
}
