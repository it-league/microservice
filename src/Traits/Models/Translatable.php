<?php

namespace ITLeague\Microservice\Traits\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

use function language;

/**
 * @property-read \Illuminate\Database\Eloquent\Model|null $translation
 * @mixin \ITLeague\Microservice\Models\EntityModel
 */
trait Translatable
{
    private bool $translationTableWasJoined = false;

    public static function bootTranslatable(): void
    {
        static::saved(
            function (self $model): void {
                $model->setTranslation($model->getUnfilledAttributes());
            }
        );
    }

    public function initializeTranslatable(): void
    {
        $this->with[] = 'translation';
    }

    public function setTranslation(array $fields): void
    {
        if ($this->translation) {
            $this->translation->update($fields);
        } else {
            $fields['language_id'] = language()->firstWhere('code', app()->getLocale())->getKey();
            $this->setRelation('translation', $this->translation()->create($fields));
        }
    }

    public function getTranslationTable(): string
    {
        return $this->translation()->getRelated()->getTable();
    }

    public function scopeJoinTranslatableTable(Builder $builder): void
    {
        if ($this->translationTableWasJoined === false) {
            $translationTable = $this->getTranslationTable();
            $modelTable = $this->getTable();
            $languageTable = language(true)->getTable();
            $builder->join($translationTable, $this->translation()->getForeignKeyName(), "$modelTable.{$this->getKeyName()}")
                ->join($languageTable, "$translationTable.language_id", "$languageTable.id")
                ->where("$languageTable.code", app()->getLocale())
                ->select("{$this->getTable()}.*");
            $this->translationTableWasJoined = true;
        }
    }

    public function scopeWhereTranslatable(Builder $builder, string $column, ?string $operator = null, string|int|null $value = null): void
    {
        $builder->joinTranslatableTable()->where("{$this->getTranslationTable()}.$column", $operator, $value);
    }

    public function scopeOrderByTranslatable(Builder $builder, string $column, string $direction = 'asc'): void
    {
        $builder->joinTranslatableTable()->orderBy("{$this->getTranslationTable()}.$column", $direction);
    }

    public function scopeOrderByDescTranslatable(Builder $builder, string $column): void
    {
        $builder->orderByTranslatable($column, 'desc');
    }

    abstract public function translation(): HasOne;
}
