<?php

class EmailValidator extends TextValidator
{
    private string $pattern;

    public function __construct(int $maxLength, string $pattern)
    {
        parent::__construct($maxLength);
        $this->pattern = $pattern;
    }

    public function validate(string $value): bool
    {
        $isValid = parent::validate($value);
        if (!$isValid) return false;

        if (!preg_match($this->pattern, $value)) {
            $this->errors[] = 'Некорректный e-mail';
            return false;
        }

        return true;
    }
}
