<?php

namespace ITLeague\Microservice\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

/**
 * ITLeague\Microservice\Http\Models\Language
 *
 * @property int $id
 * @property string $code
 * @property string $language
 * @property bool $default
 * @method static Builder|Language newModelQuery()
 * @method static Builder|Language newQuery()
 * @method static Builder|Language query()
 * @method static Builder|Language whereLanguage($value)
 * @method static Builder|Language whereCode($value)
 * @method static Builder|Language whereDefault($value)
 * @method static Builder|Language whereId($value)
 * @method static Builder|Language default()
 * @mixin \Eloquent
 */
class Language extends Model
{
    use SerializesModels;

    protected $casts = [
        'default' => 'boolean'
    ];
    public $timestamps = false;
    protected $guarded = ['id'];

    final public function scopeDefault(Builder $query): Builder
    {
        return $query->whereDefault(true)->limit(1);
    }
}
