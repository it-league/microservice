<?php


namespace ITLeague\Microservice\Observers;


use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use ITLeague\Microservice\Http\Helpers\Storage;
use ITLeague\Microservice\Models\EntityModel;

class FileAttributeObserver
{
    public function deleted(EntityModel $model): void
    {
        $attributes = $model->getAttributes();
        foreach ($model->getFiles() as $attribute => $settings) {
            $value = $attributes[$attribute] ?? '';

            // delete file
            if (Str::length($value) === 36) {
                Storage::delete($value);
            }
        }
    }

    public function saved(EntityModel $model): void
    {
        if (Auth::check() !== true) {
            throw new AuthorizationException('Can`t save file without authorization');
        }

        $attributes = $model->getAttributes();
        foreach ($model->getFiles() as $attribute => $settings) {
            $original = (string)$model->getRawOriginal($attribute);
            $value = $attributes[$attribute] ?? '';

            if ($original !== $value) {
                // confirm new file
                if (Str::length($value) === 36 && ($settings['force'] ?? false) === false) {
                    Storage::confirm($value, $settings['permission'] ?? [], $settings['sizes'] ?? []);
                }

                // delete old file
                if (Str::length($original) === 36) {
                    Storage::delete($original);
                }
            }
        }
    }

}
