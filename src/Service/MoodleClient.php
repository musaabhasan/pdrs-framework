<?php

declare(strict_types=1);

namespace Pdrs\Service;

use RuntimeException;
use Pdrs\Support\Env;

final class MoodleClient
{
    public function findUserByEmailOrUsername(string $email, string $username): ?array
    {
        foreach (['email' => $email, 'username' => $username] as $field => $value) {
            $users = $this->call('core_user_get_users_by_field', [
                'field' => $field,
                'values' => [$value],
            ]);

            if (is_array($users) && isset($users[0])) {
                return $users[0];
            }
        }

        return null;
    }

    public function createUser(array $user): int
    {
        $response = $this->call('core_user_create_users', ['users' => [$user]]);

        if (!isset($response[0]['id'])) {
            throw new RuntimeException('Moodle did not return a user id.');
        }

        return (int) $response[0]['id'];
    }

    public function enrolUser(int $userId, array $courseIds, int $roleId): void
    {
        if ($courseIds === []) {
            return;
        }

        $enrolments = array_map(
            fn (int $courseId): array => ['roleid' => $roleId, 'userid' => $userId, 'courseid' => $courseId],
            $courseIds
        );

        $this->call('enrol_manual_enrol_users', ['enrolments' => $enrolments]);
    }

    public function addToCohorts(int $userId, array $cohortIds): void
    {
        if ($cohortIds === []) {
            return;
        }

        $members = array_map(
            fn (int $cohortId): array => ['cohorttype' => ['type' => 'id', 'value' => $cohortId], 'usertype' => ['type' => 'id', 'value' => $userId]],
            $cohortIds
        );

        $this->call('core_cohort_add_cohort_members', ['members' => $members]);
    }

    private function call(string $function, array $payload): array
    {
        $baseUrl = rtrim((string) Env::get('MOODLE_BASE_URL'), '/');
        $token = (string) Env::get('MOODLE_TOKEN');
        $format = (string) Env::get('MOODLE_REST_FORMAT', 'json');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Moodle integration is not configured.');
        }

        $url = $baseUrl . '/webservice/rest/server.php';
        $form = array_merge($payload, [
            'wstoken' => $token,
            'wsfunction' => $function,
            'moodlewsrestformat' => $format,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($form),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $status >= 400) {
            throw new RuntimeException('Moodle API request failed: ' . ($error ?: 'HTTP ' . $status));
        }

        $decoded = json_decode($raw, true);

        if (isset($decoded['exception'])) {
            throw new RuntimeException('Moodle API exception: ' . ($decoded['message'] ?? $decoded['exception']));
        }

        return is_array($decoded) ? $decoded : [];
    }
}
