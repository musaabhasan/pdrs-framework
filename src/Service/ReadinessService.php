<?php

declare(strict_types=1);

namespace Pdrs\Service;

use PDO;
use Throwable;
use Pdrs\Config\AppConfig;
use Pdrs\Support\Env;

final class ReadinessService
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function check(): array
    {
        $checks = [
            'database' => $this->database(),
            'storage_logs' => $this->writablePath(__DIR__ . '/../../storage/logs'),
            'storage_cache' => $this->writablePath(__DIR__ . '/../../storage/cache'),
            'app_key' => ['ok' => strlen(AppConfig::appKey()) >= 32],
            'moodle_configured' => [
                'ok' => (string) Env::get('MOODLE_BASE_URL', '') !== '' && (string) Env::get('MOODLE_TOKEN', '') !== '',
            ],
        ];

        $ready = true;
        foreach ($checks as $check) {
            $ready = $ready && (bool) ($check['ok'] ?? false);
        }

        return [
            'status' => $ready ? 'ready' : 'degraded',
            'checked_at' => gmdate('c'),
            'checks' => $checks,
        ];
    }

    private function database(): array
    {
        try {
            $this->db->query('SELECT 1')->fetchColumn();
            return ['ok' => true];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => 'database_unavailable'];
        }
    }

    private function writablePath(string $path): array
    {
        return [
            'ok' => is_dir($path) && is_writable($path),
            'path' => basename($path),
        ];
    }
}
