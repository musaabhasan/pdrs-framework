<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;

final class MaintenanceRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function deleteExpiredVerifications(): int
    {
        return $this->delete('DELETE FROM verification_challenges WHERE verified_at IS NULL AND expires_at < UTC_TIMESTAMP()');
    }

    public function pruneRateLimits(int $olderThanHours = 48): int
    {
        $hours = max(1, min($olderThanHours, 720));

        return $this->delete("DELETE FROM rate_limits WHERE window_start < DATE_SUB(UTC_TIMESTAMP(), INTERVAL {$hours} HOUR)");
    }

    public function pruneAuditLogs(int $retentionMonths): int
    {
        $months = max(1, min($retentionMonths, 120));

        return $this->delete("DELETE FROM audit_logs WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL {$months} MONTH)");
    }

    private function delete(string $sql): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
