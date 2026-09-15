<?php

class ThemeValidator extends Validator
{
    private array $allowed = [];

    public function __construct(array $allowed = [])
    {
        $this->allowed = $allowed;
    }

    public function validate(string $value): bool
    {
        if ($value === '') {
            $this->errors[] = 'Выберите тему обращения';
            return false;
        }

        if (!in_array($value, $this->allowed, true)) {
            $this->errors[] = 'Недопустимое значение темы';
            return false;
        }

        return true;
    }
}
