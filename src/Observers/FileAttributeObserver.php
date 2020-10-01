<?php


namespace ITLeague\Microservice\Observers;


use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ITLeague\Microservice\Http\Helpers\Storage;

class FileAttributeObserver
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleted(Model $model): void
    {
        /** @var \ITLeague\Microservice\Traits\WithFileAttributes $model */
        $attributes = $model->getAttributes();
        foreach ($model->getFiles() as $attribute => $settings) {
            $value = $attributes[$attribute] ?? '';

            // delete file
            if (Str::length($value) === 36) {
                Storage::delete($value);
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function saved(Model $model): void
    {
        /** @var \ITLeague\Microservice\Traits\WithFileAttributes $model */
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
