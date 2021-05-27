<?php


namespace ITLeague\Microservice\Repositories\Interfaces;


use ITLeague\Microservice\Models\Language;
use Illuminate\Database\Eloquent\Collection;

interface LanguageRepositoryInterface
{
    public function default(): Language;

    public function all(): Collection;
}
