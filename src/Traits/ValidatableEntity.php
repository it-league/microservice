<?php


namespace ITLeague\Microservice\Traits;


use Illuminate\Support\Arr;
use Validator;

/** @mixin \ITLeague\Microservice\Models\EntityModel */
trait ValidatableEntity
{
    private static array $staticClassesRules;
    protected static array $rules;

    // TODO: попробовать доработать
    private static array $rulesValidator = [
        'store' => 'filled|array',
        'store.*' => 'required|array_or_string',
        'store.*.*' => 'required',

        'update' => 'filled|array',
        'update.*' => 'required|array_or_string',
        'update.*.*' => 'required',

        'filter' => 'filled|array',
        'filter.*' => 'required|array_or_string',
        'filter.*.*' => 'required',

        'sort' => 'filled|string'
    ];

    public static function bootValidatableEntity()
    {
        static::bootRules();
    }

    private static function bootRules(): void
    {
        $rules = self::validate(static::$rules, self::$rulesValidator);
        static::$staticClassesRules[static::class] = $rules;
    }

    /**
     * @param string $method
     *
     * @return array|string
     */
    final public static function rules(string $method)
    {
        return Arr::get(static::$staticClassesRules, static::class . '.' . $method, []);
    }

    private static function validate(array $data, array $rules): array
    {
        return Validator::make($data, $rules)->validate();
    }

    public function validateSort(array $data): array
    {
        return count($data) > 0 ? $this->validate(['sort' => $data], ['sort' => self::rules('sort')])['sort'] : [];
    }

    public function validateStore(array $data): array
    {
        return $this->validate($data, self::rules('store'));
    }

    public function validateFilter(array $data): array
    {
        return $this->validate($data, self::rules('filter'));
    }

    public function validateUpdate(array $data): array
    {
        return $this->validate($data, self::rules('update'));
    }
}
