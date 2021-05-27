<?php


namespace ITLeague\Microservice\Observers;


use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ITLeague\Microservice\Facades\Storage;

class FileAttributeObserver
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @throws \Exception
     */
    public function deleted(Model $model): void
    {
        /** @var \ITLeague\Microservice\Traits\Models\WithFileAttributes $model */
        $attributes = $model->getAttributes();
        foreach ($model->getFileAttributesSettings() as $attribute => $settings) {
            if ($model->isFileAttributeMultiple($attribute)) {
                $value = $attributes[$attribute];
                $value = $value ? json_decode($value, true) : [];

                foreach ($value as $index => $item) {
                    $this->deleteFile($item);
                }
            } else {
                $this->deleteFile($attributes[$attribute]);
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function saved(Model $model): void
    {
        /** @var \ITLeague\Microservice\Traits\Models\WithFileAttributes $model */
        if (!auth()->check()) {
            throw new AuthorizationException('Can`t save file without authorization');
        }

        $attributes = $model->getAttributes();
        foreach ($model->getFileAttributesSettings() as $attribute => $settings) {
            if ($model->isFileAttributeMultiple($attribute)) {
                $original = $model->getRawOriginal($attribute);
                $original = $original ? json_decode($original, true) : [];

                $value = $model->getUnfilledAttribute('original_' . $attribute) ?? [];

                // для новых файлов отправляется запрос confirm, для удалённых - delete
                foreach (Arr::except($value, $original) as $newValue) {
                    $this->confirmFile($newValue, $settings);
                }

                foreach (Arr::except($original, $value) as $deletedValue) {
                    $this->deleteFile($deletedValue);
                }
            } else {
                $original = (string)$model->getRawOriginal($attribute);
                $value = $attributes[$attribute] ?? '';

                // если файл заменён, то confirm для нового и delete для удалённого
                if ($original !== $value) {
                    $this->confirmFile($value, $settings);
                    $this->deleteFile($original);
                }
            }
        }
    }

    /**
     * @param string|null $attribute
     *
     * @throws \Exception
     */
    private function deleteFile(?string $attribute)
    {
        if (Str::length((string)$attribute) === 36) {
            Storage::delete($attribute);
        }
    }

    private function confirmFile(?string $attribute, array $settings)
    {
        if (Str::length((string)$attribute) === 36 && ($settings['force'] ?? false) === false) {
            Storage::confirm($attribute, $settings['permission'] ?? [], $settings['sizes'] ?? []);
        }
    }

}
