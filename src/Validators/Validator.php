<?php


namespace itleague\microservice\Validators;


use Illuminate\Contracts\Translation\Translator;

class Validator extends \Illuminate\Validation\Validator
{
    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        $this->numericRules[] = 'ArrayOrInteger';
    }

    public function validateArrayOrInteger($attribute, $value, $parameters, $validator)
    {
        if (! is_array($value)) {
            if (! $this->validateInteger($attribute, $value)) {
                $this->addFailure($attribute, 'integer', []);
            } else {
                if (! $this->validateMin($attribute, $value, [1])) {
                    $this->addFailure($attribute, 'min', [1]);
                }
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
}
