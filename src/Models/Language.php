<?php

namespace itleague\microservice\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * itleague\microservice\Http\Models\Language
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
    protected $casts = [
        'default' => 'boolean'
    ];
    public $timestamps = false;
    protected $guarded = ['id'];

    public function scopeDefault(Builder $query): Builder
    {
        return $query->whereDefault(true)->limit(1);
    }
}
