<?php


namespace ITLeague\Microservice\Repositories\Interfaces;

use ITLeague\Microservice\Models\EntityModel;
use Illuminate\Contracts\Support\Arrayable;

interface RepositoryInterface
{
    public function show($id): EntityModel;

    public function index(): Arrayable;

    public function store(array $fields): EntityModel;

    public function update($id, array $fields): EntityModel;

    public function destroy($id): ?bool;
}
