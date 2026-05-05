<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;

final class RegistrationRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO registrations
            (uuid, event_id, verification_id, email_hash, email_encrypted, first_name_encrypted, last_name_encrypted,
             city_encrypted, metadata_encrypted, approval_status, approval_reason, created_at)
            VALUES
            (:uuid, :event_id, :verification_id, :email_hash, :email_encrypted, :first_name_encrypted, :last_name_encrypted,
             :city_encrypted, :metadata_encrypted, :approval_status, :approval_reason, UTC_TIMESTAMP())'
        );
        $stmt->execute($payload);

        return (int) $this->db->lastInsertId();
    }

    public function markProvisioned(int $id, int $moodleUserId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE registrations SET approval_status = "provisioned", moodle_user_id = :moodle_user_id, provisioned_at = UTC_TIMESTAMP() WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'moodle_user_id' => $moodleUserId]);
    }

    public function markFailed(int $id, string $reason): void
    {
        $stmt = $this->db->prepare('UPDATE registrations SET approval_status = "failed", approval_reason = :reason WHERE id = :id');
        $stmt->execute(['id' => $id, 'reason' => substr($reason, 0, 500)]);
    }

    public function findByUuid(string $uuid): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, e.title AS event_title, e.start_at, e.end_at, e.timezone, e.location
             FROM registrations r
             JOIN events e ON e.id = r.event_id
             WHERE r.uuid = :uuid LIMIT 1'
        );
        $stmt->execute(['uuid' => $uuid]);
        $registration = $stmt->fetch();

        return $registration ?: null;
    }
}
