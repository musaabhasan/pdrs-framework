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

            $name = (string) $field['name'];
            $shortname = (string) $field['moodle_shortname'];
            if (
                preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name) !== 1 ||
                preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $shortname) !== 1
            ) {
                continue;
            }

            $customFields[] = [
                'type' => $shortname,
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
