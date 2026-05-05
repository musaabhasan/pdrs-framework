<?php

declare(strict_types=1);

namespace Pdrs\Middleware;

use Pdrs\Http\Request;
use Pdrs\Support\Env;

final class OperationsGuard
{
    public static function authorized(Request $request): bool
    {
        $expectedHash = strtolower((string) Env::get('OPERATIONS_TOKEN_HASH', ''));
        if ($expectedHash === '') {
            return false;
        }

        $header = (string) (
            $request->server['HTTP_AUTHORIZATION']
            ?? $request->server['REDIRECT_HTTP_AUTHORIZATION']
            ?? ''
        );

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return false;
        }

        return hash_equals($expectedHash, hash('sha256', trim($matches[1])));
    }
}
