<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;

final class RateLimitRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function hit(string $key, string $action, int $windowSeconds, int $maxAttempts): bool
    {
        $this->db->beginTransaction();

        $stmt = $this->db->prepare(
            'SELECT * FROM rate_limits WHERE rate_key = :rate_key AND action = :action FOR UPDATE'
        );
        $stmt->execute(['rate_key' => $key, 'action' => $action]);
        $row = $stmt->fetch();

        if (!$row) {
            $insert = $this->db->prepare(
                'INSERT INTO rate_limits (rate_key, action, attempts, window_start) VALUES (:rate_key, :action, 1, UTC_TIMESTAMP())'
            );
            $insert->execute(['rate_key' => $key, 'action' => $action]);
            $this->db->commit();
            return true;
        }

        $windowStart = strtotime($row['window_start'] . ' UTC');
        $expired = $windowStart === false || $windowStart + $windowSeconds < time();

        if ($expired) {
            $reset = $this->db->prepare(
                'UPDATE rate_limits SET attempts = 1, window_start = UTC_TIMESTAMP() WHERE id = :id'
            );
            $reset->execute(['id' => $row['id']]);
            $this->db->commit();
            return true;
        }

        if ((int) $row['attempts'] >= $maxAttempts) {
            $this->db->commit();
            return false;
        }

        $update = $this->db->prepare('UPDATE rate_limits SET attempts = attempts + 1 WHERE id = :id');
        $update->execute(['id' => $row['id']]);
        $this->db->commit();

        return true;
    }
}
