<?php

declare(strict_types=1);

namespace Pdrs\Service;

use DateTimeImmutable;
use Pdrs\Support\Validator;
use Pdrs\Support\View;

final class EventFormService
{
    private const STANDARD_FIELDS = ['verification_id', 'verification_signature', 'first_name', 'last_name'];

    public function registrationErrors(array $event, array $payload): array
    {
        $errors = Validator::required($payload, self::STANDARD_FIELDS);

        foreach (['first_name', 'last_name', 'city'] as $field) {
            if (isset($payload[$field]) && strlen((string) $payload[$field]) > 120) {
                $errors[$field] = 'Please keep this value under 120 characters.';
            }
        }

        foreach ($event['custom_fields'] ?? [] as $field) {
            $name = $this->fieldName($field);
            if ($name === null) {
                continue;
            }

            $value = trim((string) ($payload[$name] ?? ''));
            if (!empty($field['required']) && $value === '') {
                $errors[$name] = 'This field is required.';
                continue;
            }

            if ($value === '') {
                continue;
            }

            if (strlen($value) > 500) {
                $errors[$name] = 'Please keep this value under 500 characters.';
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');
            if ($type === 'email' && !Validator::email($value)) {
                $errors[$name] = 'Please enter a valid email address.';
            }

            if ($type === 'number' && !is_numeric($value)) {
                $errors[$name] = 'Please enter a valid number.';
            }

            if ($type === 'date' && !$this->validDate($value)) {
                $errors[$name] = 'Please enter a valid date.';
            }

            if ($type === 'select' && !$this->validOption($field, $value)) {
                $errors[$name] = 'Please select a valid option.';
            }
        }

        return $errors;
    }

    public function renderCustomFields(array $event, bool $includeInputs): string
    {
        if (!$includeInputs) {
            return '';
        }

        $html = '';
        foreach ($event['custom_fields'] ?? [] as $field) {
            $name = $this->fieldName($field);
            if ($name === null) {
                continue;
            }

            $safeName = View::e($name);
            $label = View::e((string) ($field['label'] ?? $name));
            $required = !empty($field['required']) ? 'required' : '';
            $type = (string) ($field['type'] ?? 'text');

            if ($type === 'textarea') {
                $html .= "<label>{$label} <textarea {$required} name=\"{$safeName}\" rows=\"4\"></textarea></label>";
                continue;
            }

            if ($type === 'select' && is_array($field['options'] ?? null)) {
                $options = '<option value="">Select an option</option>';
                foreach ($field['options'] as $option) {
                    $value = View::e((string) $option);
                    $options .= "<option value=\"{$value}\">{$value}</option>";
                }
                $html .= "<label>{$label} <select {$required} name=\"{$safeName}\">{$options}</select></label>";
                continue;
            }

            $inputType = in_array($type, ['text', 'email', 'tel', 'number', 'date'], true) ? $type : 'text';
            $html .= "<label>{$label} <input {$required} type=\"{$inputType}\" name=\"{$safeName}\"></label>";
        }

        return $html;
    }

    private function fieldName(array $field): ?string
    {
        $name = (string) ($field['name'] ?? '');

        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name) === 1 ? $name : null;
    }

    private function validOption(array $field, string $value): bool
    {
        $options = array_map('strval', is_array($field['options'] ?? null) ? $field['options'] : []);

        return in_array($value, $options, true);
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
