<?php

declare(strict_types=1);

namespace Pdrs\Config;

use Pdrs\Support\Env;

final class AppConfig
{
    public static function appName(): string
    {
        return (string) Env::get('APP_NAME', 'Professional Development Registration System');
    }

    public static function appUrl(): string
    {
        return rtrim((string) Env::get('APP_URL', 'http://localhost:8080'), '/');
    }

    public static function timezone(): string
    {
        return (string) Env::get('APP_TIMEZONE', 'Asia/Dubai');
    }

    public static function appKey(): string
    {
        $key = (string) Env::get('APP_KEY', '');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            return $decoded !== false ? $decoded : '';
        }

        return $key;
    }

    public static function databaseDsn(): string
    {
        $host = Env::get('DB_HOST', 'mysql');
        $port = Env::get('DB_PORT', '3306');
        $db = Env::get('DB_DATABASE', 'pdrs');

        return sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);
    }

    public static function databaseUser(): string
    {
        return (string) Env::get('DB_USERNAME', 'pdrs');
    }

    public static function databasePassword(): string
    {
        return (string) Env::get('DB_PASSWORD', 'pdrs_dev_password');
    }
}
