<?php


namespace ITLeague\Microservice\Traits;


use ITLeague\Microservice\Casts\File;
use ITLeague\Microservice\Observers\FileAttributeObserver;


/** @mixin \Illuminate\Database\Eloquent\Model */
trait WithFileAttributes
{
    public function initializeWithFileAttributes(): void
    {
        foreach ($this->getFiles() as $attribute => $settings) {
            $this->mergeCasts([$attribute => File::class]);
        }
    }

    public static function bootWithFileAttributes(): void
    {
        $instance = new static();
        if (count($instance->getFiles()) > 0) {
            $instance->registerObserver(FileAttributeObserver::class);
        }
    }

    /**
     * @return array
     */
    final public function getFiles(): array
    {
        return $this->files ?? [];
    }

}
