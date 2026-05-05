<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;
use Pdrs\Support\Json;

final class AuditRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function record(array $event): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs
            (actor_type, actor_id, action, entity_type, entity_id, ip_hash, user_agent_hash, payload_json, created_at)
            VALUES
            (:actor_type, :actor_id, :action, :entity_type, :entity_id, :ip_hash, :user_agent_hash, :payload_json, UTC_TIMESTAMP())'
        );
        $stmt->execute([
            'actor_type' => $event['actor_type'] ?? 'system',
            'actor_id' => $event['actor_id'] ?? null,
            'action' => $event['action'],
            'entity_type' => $event['entity_type'] ?? null,
            'entity_id' => $event['entity_id'] ?? null,
            'ip_hash' => $event['ip_hash'] ?? null,
            'user_agent_hash' => $event['user_agent_hash'] ?? null,
            'payload_json' => Json::encode($event['payload'] ?? []),
        ]);
    }
}
