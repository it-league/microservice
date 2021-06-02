<?php


namespace ITLeague\Microservice\Traits\Models;


use Illuminate\Support\Arr;

trait WithUnfilledAttributes
{
    private array $unfilled = [];

    /**
     * Сохраняет переданные, но незаполненные в текущей модели атрибуты в спец. свойство
     * Эти атрибуты могут использоваться, например, для сохранения релэйшенов или переводов через систему событий
     */
    public function fill(array $attributes): self
    {
        $result = parent::fill($attributes);
        $this->mergeUnfilled($attributes);
        return $result;
    }

    public function mergeUnfilled(array $attributes): void
    {
        $unfilled = count($attributes) ? Arr::except($attributes, array_keys($this->attributes)) : [];
        $this->unfilled = array_merge($this->unfilled, $unfilled);
    }

    public function getUnfilledAttribute(string $attribute): mixed
    {
        return Arr::get($this->unfilled, $attribute);
    }

    public function getUnfilledAttributes(): array
    {
        return $this->unfilled;
    }
}
