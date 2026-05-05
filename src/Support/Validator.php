<?php

declare(strict_types=1);

namespace Pdrs\Support;

final class Validator
{
    public static function required(array $payload, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!isset($payload[$field]) || trim((string) $payload[$field]) === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        return $errors;
    }

    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function slug(string $value): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1;
    }
}
