<?php

declare(strict_types=1);

namespace Pdrs\Support;

use PDO;
use Pdrs\Config\AppConfig;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                AppConfig::databaseDsn(),
                AppConfig::databaseUser(),
                AppConfig::databasePassword(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$pdo;
    }
}
