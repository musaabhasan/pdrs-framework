<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Http\Request;
use Pdrs\Repository\RateLimitRepository;
use Pdrs\Support\Env;

final class RateLimiter
{
    public function __construct(
        private readonly RateLimitRepository $repository,
        private readonly CryptoService $crypto,
    ) {
    }

    public function allow(Request $request, string $action, ?string $email = null): bool
    {
        $identifier = $request->ip() . '|' . strtolower((string) $email);
        $key = $this->crypto->hash($identifier);

        return $this->repository->hit(
            $key,
            $action,
            Env::int('RATE_LIMIT_WINDOW_SECONDS', 900),
            Env::int('RATE_LIMIT_MAX_ATTEMPTS', 5)
        );
    }
}
