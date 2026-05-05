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
        if (!is_dir($path)) {
            return ['ok' => false, 'path' => basename($path)];
        }

        $probe = $path . DIRECTORY_SEPARATOR . '.readiness-' . bin2hex(random_bytes(6));
        $written = @file_put_contents($probe, 'ok') === 2;

        if ($written) {
            @unlink($probe);
        }

        return ['ok' => $written, 'path' => basename($path)];
    }
}
