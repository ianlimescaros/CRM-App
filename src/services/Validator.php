<?php

class Validator
{
    public static function stringLength(string $value, string $field, int $min = 0, int $max = 255): array
    {
        $len = mb_strlen($value);
        if ($len < $min || $len > $max) {
            return [$field => "Must be between {$min} and {$max} characters."];
        }
        return [];
    }

    public static function numeric(mixed $value, string $field): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (!is_numeric($value)) {
            return [$field => 'Must be a number.'];
        }
        return [];
    }

    public static function dateYmd(mixed $value, string $field): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $d = DateTime::createFromFormat('Y-m-d', (string)$value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            return [$field => 'Invalid date format (Y-m-d).'];
        }
        return [];
    }

    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $errors[$field] = 'This field is required.';
            }
        }
        return $errors;
    }

    public static function email(string $value, string $field = 'email'): array
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [$field => 'Invalid email format.'];
        }
        return [];
    }

    public static function inEnum(string $value, array $allowed, string $field): array
    {
        if (!in_array($value, $allowed, true)) {
            return [$field => 'Invalid value.'];
        }
        return [];
    }
}
