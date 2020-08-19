<?php


namespace itleague\microservice\Repositories;


use itleague\microservice\Models\Language;
use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
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
