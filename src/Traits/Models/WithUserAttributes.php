<?php


namespace ITLeague\Microservice\Traits\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


/**
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $created_by
 * @property string $updated_by
 *
 * @method static Builder|self whereCreatedAt(Carbon $value)
 * @method static Builder|self whereCreatedBy(string $value)
 * @method static Builder|self whereUpdatedAt(Carbon $value)
 * @method static Builder|self whereUpdatedBy(string $value)
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithUserAttributes
{
    public function initializeWithUserAttributes(): void
    {
        $this->mergeGuarded(['created_at', 'created_by', 'updated_at', 'updated_by']);
    }

    public static function bootWithUserAttributes(): void
    {
        static::creating(
            function (self $model): void {
                $model->created_by = auth()->check() ? auth()->id() : null;
                $model->updated_by = $model->created_by;
            }
        );
        static::updating(
            function (self $model): void {
                $model->updated_by = auth()->check() ? auth()->id() : null;
            }
        );
    }

}
