<?php


namespace itleague\microservice\Mixins;


use Illuminate\Database\Schema\Blueprint;

class BlueprintMixin
{
    public function softDeletesWithUserFields()
    {
        return function () {
            /** @var Blueprint $this */
            $this->softDeletes();
            $this->uuid('deleted_by')->nullable();
        };
    }

    public function timestampsWithUserFields()
    {
        return function () {
            /** @var Blueprint $this */
            $this->timestamp('created_at')->useCurrent();
            $this->timestamp('updated_at')->useCurrent();
            $this->uuid('created_by');
            $this->uuid('updated_by');
        };
    }

    public function foreignLanguageId()
    {
        return function () {
            /** @var Blueprint $this */
            $this->unsignedTinyInteger('language_id');
            $this->foreign('language_id')->references('id')->on('languages')->onDelete('restrict');
        };
    }
}
