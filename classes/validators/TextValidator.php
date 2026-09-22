<?php

namespace app\validators;

class TextValidator extends Validator
{
    protected int $maxLength;

    public function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function validate(string $value): bool
    {
        if ($value === '') {
            $this->errors[] = 'Введите текст';
            return false;
        }

        if (mb_strlen($value) > $this->maxLength) {
            $this->errors[] = 'Максимум ' . $this->maxLength . ' символов';
            return false;
        }

        return true;
    }
}
