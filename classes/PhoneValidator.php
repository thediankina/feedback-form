<?php

class PhoneValidator extends Validator
{
    public string $invalidFormatErrorMessage = 'Неверный формат номера телефона';
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function validate(string $value): bool
    {
        if ($value === '') {
            $this->errors[] = 'Введите номер телефона';
            return false;
        }

        if (!preg_match($this->pattern, $value)) {
            $this->errors[] = $this->invalidFormatErrorMessage;
            return false;
        }

        return true;
    }
}
