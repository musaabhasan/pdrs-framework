<?php

declare(strict_types=1);

namespace Pdrs\Middleware;

use Pdrs\Support\Env;

final class SecurityHeaders
{
    public static function apply(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; style-src 'self'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'");

        if (Env::bool('SECURITY_FORCE_HTTPS', false)) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
