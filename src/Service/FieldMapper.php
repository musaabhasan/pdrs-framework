<?php

declare(strict_types=1);

namespace Pdrs\Service;

final class FieldMapper
{
    public function moodleUser(array $event, array $registration): array
    {
        $username = $this->username($registration['email']);
        $password = bin2hex(random_bytes(12)) . 'aA1!';

        $user = [
            'username' => $username,
            'password' => $password,
            'firstname' => $registration['first_name'],
            'lastname' => $registration['last_name'],
            'email' => $registration['email'],
            'city' => $registration['city'] ?? '',
            'auth' => 'manual',
            'preferences' => [
                ['type' => 'auth_forcepasswordchange', 'value' => '1'],
            ],
        ];

        $customFields = [];
        foreach ($event['custom_fields'] ?? [] as $field) {
            if (!isset($field['moodle_shortname'], $field['name'])) {
                continue;
            }

            $name = $field['name'];
            $customFields[] = [
                'type' => $field['moodle_shortname'],
                'value' => (string) ($registration['metadata'][$name] ?? ''),
            ];
        }

        if ($customFields !== []) {
            $user['customfields'] = $customFields;
        }

        return [$user, $password];
    }

    public function username(string $email): string
    {
        return strtolower(preg_replace('/[^a-z0-9._-]+/i', '.', strstr($email, '@', true) ?: $email));
    }
}
