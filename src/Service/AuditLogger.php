<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Http\Request;
use Pdrs\Repository\AuditRepository;

final class AuditLogger
{
    public function __construct(
        private readonly AuditRepository $repository,
        private readonly CryptoService $crypto,
    ) {
    }

    public function record(string $action, Request $request, array $context = []): void
    {
        $this->repository->record([
            'actor_type' => $context['actor_type'] ?? 'public',
            'actor_id' => $context['actor_id'] ?? null,
            'action' => $action,
            'entity_type' => $context['entity_type'] ?? null,
            'entity_id' => $context['entity_id'] ?? null,
            'ip_hash' => $this->crypto->hash($request->ip()),
            'user_agent_hash' => $this->crypto->hash($request->userAgent()),
            'payload' => $context['payload'] ?? [],
        ]);
    }
}
