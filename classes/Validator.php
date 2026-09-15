<?php

abstract class Validator
{
    protected array $errors = [];

    abstract public function validate(string $value): bool;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsAsString(): string
    {
        return implode(', ', $this->errors);
    }
}
