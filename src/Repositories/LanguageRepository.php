<?php


namespace ITLeague\Microservice\Repositories;


use Illuminate\Database\Eloquent\Collection;
use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;

final class LanguageRepository implements LanguageRepositoryInterface
{
    private Language $model;

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
