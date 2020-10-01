<?php


namespace ITLeague\Microservice\Traits;

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
    public function initializeWithUserFieldsTrait(): void
    {
        $this->mergeGuarded(['created_at', 'created_by', 'updated_at', 'updated_by']);
    }

    protected static function bootWithUserFields(): void
    {
        static::creating(
            function (self $model) {
                $model->created_by = Auth::check() ? Auth::id() : null;
                $model->updated_by = $model->created_by;
            }
        );
        static::updating(
            function (self $model) {
                $model->updated_by = Auth::check() ? Auth::id() : null;
            }
        );
    }

}
