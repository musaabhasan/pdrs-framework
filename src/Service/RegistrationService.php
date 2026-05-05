<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Repository\RegistrationRepository;
use Pdrs\Support\Json;
use Pdrs\Support\Uuid;

final class RegistrationService
{
    public function __construct(
        private readonly RegistrationRepository $registrations,
        private readonly CryptoService $crypto,
        private readonly ApprovalService $approval,
        private readonly ProvisioningService $provisioning,
        private readonly NotificationService $notifications,
    ) {
    }

    public function register(array $event, array $verification, array $payload): array
    {
        $email = strtolower(trim($verification['email']));
        $emailHash = $this->crypto->hash($email);
        $existingRegistration = $this->registrations->findByEventAndEmailHash((int) $event['id'], $emailHash);

        if ($existingRegistration) {
            $this->notifications->sendConfirmation(
                $email,
                $event,
                "/calendar/{$existingRegistration['uuid']}.ics",
                (string) $existingRegistration['approval_status'],
                'existing_registration'
            );

            return [
                'id' => (int) $existingRegistration['id'],
                'uuid' => (string) $existingRegistration['uuid'],
                'status' => (string) $existingRegistration['approval_status'],
                'reason' => 'existing_registration',
            ];
        }

        $decision = $this->approval->evaluate($event, $email, (bool) ($payload['payment_confirmed'] ?? false));
        $metadata = $this->metadata($event, $payload);
        $uuid = Uuid::v4();

        $id = $this->registrations->create([
            'uuid' => $uuid,
            'event_id' => $event['id'],
            'verification_id' => $verification['id'],
            'email_hash' => $emailHash,
            'email_encrypted' => $this->crypto->encrypt($email),
            'first_name_encrypted' => $this->crypto->encrypt((string) $payload['first_name']),
            'last_name_encrypted' => $this->crypto->encrypt((string) $payload['last_name']),
            'city_encrypted' => $this->crypto->encrypt((string) ($payload['city'] ?? '')),
            'metadata_encrypted' => $this->crypto->encrypt(Json::encode($metadata)),
            'approval_status' => $decision['status'],
            'approval_reason' => $decision['reason'],
        ]);

        if ($decision['status'] === 'approved') {
            $this->provisioning->provision($id, $event, [
                'email' => $email,
                'first_name' => (string) $payload['first_name'],
                'last_name' => (string) $payload['last_name'],
                'city' => (string) ($payload['city'] ?? ''),
                'metadata' => $metadata,
            ]);
        }

        $this->notifications->sendConfirmation($email, $event, "/calendar/{$uuid}.ics", $decision['status'], $decision['reason']);

        return ['id' => $id, 'uuid' => $uuid, 'status' => $decision['status'], 'reason' => $decision['reason']];
    }

    private function metadata(array $event, array $payload): array
    {
        $metadata = [];
        foreach ($event['custom_fields'] ?? [] as $field) {
            $name = (string) ($field['name'] ?? '');
            if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name) !== 1) {
                continue;
            }

            $metadata[$name] = trim((string) ($payload[$name] ?? ''));
        }

        return $metadata;
    }
}
