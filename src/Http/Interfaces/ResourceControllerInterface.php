<?php


namespace ITLeague\Microservice\Http\Interfaces;


use Illuminate\Http\JsonResponse;

interface ResourceControllerInterface
{
    public function show(string|int $id): JsonResponse;
    public function index(): JsonResponse;
    public function store(): JsonResponse;
    public function update(string|int $id): JsonResponse;
    public function destroy(string|int $id): JsonResponse;
}
