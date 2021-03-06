<?php


namespace ITLeague\Microservice\Traits\Models;


use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $deleted_at
 * @property string|null $deleted_by
 * @method static Builder|self whereDeletedAt(Carbon $value)
 * @method static Builder|self whereDeletedBy(string $value)
 */
trait WithSoftDeleting
{
    use SoftDeletes {
        SoftDeletes::bootSoftDeletes as parentBootSoftDeletes;
    }

    public function initializeWithSoftDeleting(): void
    {
        $this->mergeGuarded([$this->getDeletedAtColumn(), 'deleted_by']);
    }

    public static function bootWithSoftDeleting(): void
    {
        static::parentBootSoftDeletes();

        static::deleting(
            function (self $model) {
                $model->deleted_by = auth()->check() ? auth()->id() : null;
            }
        );

        static::restoring(
            function (self $model) {
                $model->deleted_by = null;
            }
        );
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [
            $this->getDeletedAtColumn() => $this->fromDateTime($time)
        ];

        $this->{$this->getDeletedAtColumn()} = $time;

        /**
         * Add changes from observer here,
         * overrides $columns, but leaves timestamps in tact
         */
        $columns = array_merge($query->getModel()->getDirty(), $columns);

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }

}
