<?php


namespace ITLeague\Microservice\Traits\Models;


use Illuminate\Support\Arr;
use ITLeague\Microservice\Casts\FileCast;
use ITLeague\Microservice\Observers\FileAttributeObserver;
use ITLeague\Microservice\Scopes\MultipleFileAttributeScope;


/** @mixin \Illuminate\Database\Eloquent\Model */
trait WithFileAttributes
{
    public function initializeWithFileAttributes(): void
    {
        foreach ($this->getFileAttributesSettings() as $attribute => $settings) {
            $this->mergeCasts([$attribute => FileCast::class]);
        }
    }

    public static function bootWithFileAttributes(): void
    {
        $instance = new static();
        if (count($instance->getFileAttributesSettings()) > 0) {
            $instance->registerObserver(FileAttributeObserver::class);
            static::addGlobalScope(new MultipleFileAttributeScope());
        }
    }

    final public function getFileAttributesSettings(): array
    {
        return $this->files ?? [];
    }

    final public function getFileAttributeSettings(string $attribute): ?array
    {
        return Arr::get($this->getFileAttributesSettings(), $attribute);
    }

    public function isFileAttributeMultiple(string $attribute): bool
    {
        return $this->getFileAttributeSettings($attribute)['is_multiple'] ?? false;
    }

}
