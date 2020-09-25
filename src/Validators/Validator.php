<?php


namespace ITLeague\Microservice\Validators;


use Illuminate\Contracts\Translation\Translator;

class Validator extends \Illuminate\Validation\Validator
{
    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        $this->numericRules[] = 'ArrayOrInteger';
    }

    public function validateArrayOrInteger($attribute, $value, $parameters, $validator): bool
    {
        if (! is_array($value)) {
            if (! $this->validateInteger($attribute, $value)) {
                $this->addFailure($attribute, 'integer', []);
            } elseif (! $this->validateMin($attribute, $value, [1])) {
                $this->addFailure($attribute, 'min', [1]);
            }
        }

        return true;
    }

    public function validateArrayOrString(string $attribute, $value, array $parameters, $validator): bool
    {
        if (! is_array($value)) {
            $maxParameters = count($parameters) > 0 ? $parameters : [255];
            if (! $this->validateString($attribute, $value)) {
                $this->addFailure($attribute, 'string', []);
            } elseif (! $this->validateMax($attribute, (string)$value, $maxParameters)) {
                $this->addFailure($attribute, 'min', $maxParameters);
            }
        }

        return true;
    }

    public function validateSortIn(string $attribute, $value, array $parameters, $validator): bool
    {
        $this->requireParameterCount(1, $parameters, 'sort_in');
        $this->addRules([$attribute => 'Array']);

        if (! $this->validateFilled($attribute, $value)) {
            $this->addFailure($attribute, 'filled', []);
        } elseif (! $this->validateArray($attribute, $value)) {
            $this->addFailure($attribute, 'array', []);
        } elseif (! $this->validateIn($attribute, $value, $parameters)) {
            $this->addFailure($attribute, 'in', $parameters);
        }

        return true;
    }
}
