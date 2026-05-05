<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;

final class VerificationRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO verification_challenges
            (uuid, event_id, email_hash, email_encrypted, code_hash, signed_token_hash, expires_at, ip_hash, user_agent_hash, created_at)
            VALUES
            (:uuid, :event_id, :email_hash, :email_encrypted, :code_hash, :signed_token_hash, :expires_at, :ip_hash, :user_agent_hash, UTC_TIMESTAMP())'
        );
        $stmt->execute($payload);

        return (int) $this->db->lastInsertId();
    }

    public function findValidByTokenHash(string $hash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM verification_challenges
             WHERE signed_token_hash = :hash AND verified_at IS NULL AND expires_at > UTC_TIMESTAMP()
             LIMIT 1'
        );
        $stmt->execute(['hash' => $hash]);
        $challenge = $stmt->fetch();

        return $challenge ?: null;
    }

    public function findValidByEmailHash(int $eventId, string $emailHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM verification_challenges
             WHERE event_id = :event_id AND email_hash = :email_hash AND verified_at IS NULL AND expires_at > UTC_TIMESTAMP()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['event_id' => $eventId, 'email_hash' => $emailHash]);
        $challenge = $stmt->fetch();

        return $challenge ?: null;
    }

    public function findVerifiedById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM verification_challenges WHERE id = :id AND verified_at IS NOT NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $challenge = $stmt->fetch();

        return $challenge ?: null;
    }

    public function markVerified(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE verification_challenges SET verified_at = UTC_TIMESTAMP() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function incrementAttempts(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE verification_challenges SET attempts = attempts + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
