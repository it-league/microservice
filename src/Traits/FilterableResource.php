<?php


namespace ITLeague\Microservice\Traits;


use Illuminate\Support\Arr;
use Validator;

trait FilterableResource
{
    private function fields(array $data): array
    {
        return Arr::only($data, static::validateFields(array_keys($data)));
    }

    private static function validateFields(array $fields): array
    {
        static $validatedFields;

        if (! isset($validatedFields)) {
            $requestFields = request()->fields();
            $validatedFields = is_null($requestFields) ? $fields : Validator::make(
                ['fields' => $requestFields],
                ['fields' => 'lt:1|filled|array|in:' . implode(',', $fields)]
            )->validate()['fields'];
        }

        return $validatedFields;
    }
}
