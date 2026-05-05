<?php

declare(strict_types=1);

use Pdrs\Repository\MaintenanceRepository;
use Pdrs\Support\Database;
use Pdrs\Support\Env;

require __DIR__ . '/../src/bootstrap.php';

$maintenance = new MaintenanceRepository(Database::connection());

$result = [
    'expired_verifications_deleted' => $maintenance->deleteExpiredVerifications(),
    'rate_limits_pruned' => $maintenance->pruneRateLimits(Env::int('RATE_LIMIT_PRUNE_HOURS', 48)),
    'audit_logs_pruned' => $maintenance->pruneAuditLogs(Env::int('AUDIT_RETENTION_MONTHS', 12)),
    'completed_at' => gmdate('c'),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
