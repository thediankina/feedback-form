<?php

class FullNameValidator extends Validator
{
    public function validate(string $value): bool
    {
        if ($value === '') {
            $this->errors[] = 'Введите ФИО';
            return false;
        }

        if (mb_strlen($value) > TEXT_MAX) {
            $this->errors[] = 'Максимум ' . TEXT_MAX . ' символов';
            return false;
        }

        return true;
    }
}
