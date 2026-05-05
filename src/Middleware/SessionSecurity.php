<?php

declare(strict_types=1);

namespace Pdrs\Middleware;

use Pdrs\Support\Env;

final class SessionSecurity
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = Env::bool('SECURITY_FORCE_HTTPS', false)
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        $cookieParams = [
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        $domain = (string) Env::get('SECURITY_COOKIE_DOMAIN', '');
        if ($domain !== '') {
            $cookieParams['domain'] = $domain;
        }

        session_name('pdrs_session');
        session_set_cookie_params($cookieParams);

        session_start();
    }
}
