<?php


namespace ITLeague\Microservice\Casts;


use DB;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;
use ITLeague\Microservice\Facades\Storage;

class FileCast implements CastsAttributes
{

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|null
     * @throws \Exception
     */
    final public function get($model, string $key, $value, array $attributes): ?array
    {
        /** @var \ITLeague\Microservice\Traits\Models\WithFileAttributes $model */
        if ($model->isFileAttributeMultiple($key)) {
            $value = $value ? json_decode($value, true) : [];

            foreach ($value as $index => $item) {
                $value[$index] = $this->getFileInfo($item);
            }

            return array_filter($value);
        } else {
            return $this->getFileInfo($value);
        }
    }

    final public function set($model, string $key, $value, array $attributes)
    {
        /** @var \ITLeague\Microservice\Traits\Models\WithFileAttributes $model */
        if ($model->isFileAttributeMultiple($key)) {
            $json = json_encode($value);
            $model->mergeUnfilled(['original_' . $key => $value]);
            return DB::raw("json_to_array('$json')::uuid[]");
        } else {
            return $value;
        }
    }

    private function getFileInfo(?string $value): ?array
    {
        if (Str::length((string)$value) === 36) {
            return Storage::info($value);
        }
        return null;
    }
}
