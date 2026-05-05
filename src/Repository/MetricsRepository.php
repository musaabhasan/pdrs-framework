<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;

final class MetricsRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function snapshot(): array
    {
        return [
            'events' => [
                'published' => $this->count('SELECT COUNT(*) FROM events WHERE status = "published"'),
                'draft' => $this->count('SELECT COUNT(*) FROM events WHERE status = "draft"'),
                'archived' => $this->count('SELECT COUNT(*) FROM events WHERE status = "archived"'),
            ],
            'registrations' => [
                'last_24_hours' => $this->count('SELECT COUNT(*) FROM registrations WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)'),
                'pending' => $this->count('SELECT COUNT(*) FROM registrations WHERE approval_status = "pending"'),
                'approved' => $this->count('SELECT COUNT(*) FROM registrations WHERE approval_status = "approved"'),
                'provisioned' => $this->count('SELECT COUNT(*) FROM registrations WHERE approval_status = "provisioned"'),
                'failed' => $this->count('SELECT COUNT(*) FROM registrations WHERE approval_status = "failed"'),
            ],
            'verification' => [
                'open_challenges' => $this->count('SELECT COUNT(*) FROM verification_challenges WHERE verified_at IS NULL AND expires_at > UTC_TIMESTAMP()'),
                'expired_challenges' => $this->count('SELECT COUNT(*) FROM verification_challenges WHERE verified_at IS NULL AND expires_at <= UTC_TIMESTAMP()'),
            ],
            'audit' => [
                'last_24_hours' => $this->count('SELECT COUNT(*) FROM audit_logs WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)'),
            ],
        ];
    }

    private function count(string $sql): int
    {
        return (int) $this->db->query($sql)->fetchColumn();
    }
}
