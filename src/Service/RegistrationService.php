<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Throwable;
use Pdrs\Repository\RegistrationRepository;
use Pdrs\Support\Env;
use Pdrs\Support\Json;
use Pdrs\Support\Uuid;

final class RegistrationService
{
    public function __construct(
        private readonly RegistrationRepository $registrations,
        private readonly CryptoService $crypto,
        private readonly ApprovalService $approval,
        private readonly FieldMapper $mapper,
        private readonly MoodleClient $moodle,
        private readonly NotificationService $notifications,
    ) {
    }

    public function register(array $event, array $verification, array $payload): array
    {
        $email = strtolower(trim($verification['email']));
        $decision = $this->approval->evaluate($event, $email, (bool) ($payload['payment_confirmed'] ?? false));
        $metadata = $this->metadata($event, $payload);
        $uuid = Uuid::v4();

        $id = $this->registrations->create([
            'uuid' => $uuid,
            'event_id' => $event['id'],
            'verification_id' => $verification['id'],
            'email_hash' => $this->crypto->hash($email),
            'email_encrypted' => $this->crypto->encrypt($email),
            'first_name_encrypted' => $this->crypto->encrypt((string) $payload['first_name']),
            'last_name_encrypted' => $this->crypto->encrypt((string) $payload['last_name']),
            'city_encrypted' => $this->crypto->encrypt((string) ($payload['city'] ?? '')),
            'metadata_encrypted' => $this->crypto->encrypt(Json::encode($metadata)),
            'approval_status' => $decision['status'],
            'approval_reason' => $decision['reason'],
        ]);

        if ($decision['status'] === 'approved') {
            $this->provision($id, $event, [
                'email' => $email,
                'first_name' => (string) $payload['first_name'],
                'last_name' => (string) $payload['last_name'],
                'city' => (string) ($payload['city'] ?? ''),
                'metadata' => $metadata,
            ]);
        }

        $this->notifications->sendConfirmation($email, $event, "/calendar/{$uuid}.ics");

        return ['id' => $id, 'uuid' => $uuid, 'status' => $decision['status'], 'reason' => $decision['reason']];
    }

    private function provision(int $registrationId, array $event, array $registration): void
    {
        try {
            [$user, $temporaryPassword] = $this->mapper->moodleUser($event, $registration);
            $existing = $this->moodle->findUserByEmailOrUsername($registration['email'], $user['username']);
            $moodleUserId = $existing ? (int) $existing['id'] : $this->moodle->createUser($user);

            $courseIds = array_map('intval', $event['moodle_course_ids'] ?? []);
            $cohortIds = array_map('intval', $event['moodle_cohort_ids'] ?? []);
            $this->moodle->enrolUser($moodleUserId, $courseIds, Env::int('MOODLE_STUDENT_ROLE_ID', 5));
            $this->moodle->addToCohorts($moodleUserId, $cohortIds);
            $this->registrations->markProvisioned($registrationId, $moodleUserId);

            if (!$existing) {
                $this->notifications->sendCredentialsNotice($registration['email'], $event);
            }
        } catch (Throwable $exception) {
            $this->registrations->markFailed($registrationId, $exception->getMessage());
        }
    }

    private function metadata(array $event, array $payload): array
    {
        $metadata = [];
        foreach ($event['custom_fields'] ?? [] as $field) {
            if (!isset($field['name'])) {
                continue;
            }
            $metadata[$field['name']] = (string) ($payload[$field['name']] ?? '');
        }

        return $metadata;
    }
}
