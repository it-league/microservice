<?php


namespace itleague\microservice\Repositories\Interfaces;


use itleague\microservice\Models\Language;
use Illuminate\Database\Eloquent\Collection;

interface LanguageRepositoryInterface
{
    public function default(): Language;

    public function all(): Collection;
}
