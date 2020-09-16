<?php


namespace ITLeague\Microservice\Http\Interfaces;


use Illuminate\Http\JsonResponse;

interface ResourceControllerInterface
{
    public function show($id): JsonResponse;
    public function index(): JsonResponse;
    public function store(): JsonResponse;
    public function update($id): JsonResponse;
    public function destroy($id): JsonResponse;
}
