<?php


namespace ITLeague\Microservice\Repositories;


use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LanguageRepository implements LanguageRepositoryInterface
{
    protected Language $model;

    public function __construct(Language $model)
    {
        $this->model = $model;
    }

    public function default(): Language
    {
        return $this->model->default()->firstOrFail();
    }

    public function all(): Collection
    {
        return $this->model->get();
    }
}
