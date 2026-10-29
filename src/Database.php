<?php

declare(strict_types=1);

namespace App;

use mysqli;

final class Database
{
    public static function connect(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $connection = new mysqli(
            self::environment('DB_HOST', '127.0.0.1'),
            self::environment('DB_USER', 'auth_user'),
            self::environment('DB_PASSWORD', 'change_me'),
            self::environment('DB_NAME', 'auth_app'),
            (int) self::environment('DB_PORT', '3306')
        );
        $connection->set_charset('utf8mb4');

        return $connection;
    }

    private static function environment(string $name, string $default): string
    {
        $value = getenv($name);

        return $value === false || $value === '' ? $default : $value;
    }
}
