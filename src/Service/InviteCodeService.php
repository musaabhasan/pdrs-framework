<?php

declare(strict_types=1);

namespace Pdrs\Service;

final class InviteCodeService
{
    public function __construct(private readonly CryptoService $crypto)
    {
    }

    public function enabled(array $event): bool
    {
        return (int) ($event['invite_code_enabled'] ?? 0) === 1;
    }

    public function valid(array $event, string $code): bool
    {
        if (!$this->enabled($event)) {
            return true;
        }

        $hash = (string) ($event['invite_code_hash'] ?? '');
        if ($hash === '') {
            return false;
        }

        $normalized = $this->normalize($code);
        if ($normalized === '') {
            return false;
        }

        return hash_equals($hash, $this->crypto->hash($normalized));
    }

    public function normalize(string $code): string
    {
        return preg_replace('/\s+/', '', strtolower(trim($code))) ?? '';
    }
}
