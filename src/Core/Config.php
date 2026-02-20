<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    public static function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value === false || $value === null ? $default : $value;
    }

    public static function db(): array
    {
        return [
            'host' => self::env('DB_HOST', '127.0.0.1'),
            'port' => self::env('DB_PORT', '3306'),
            'name' => self::env('DB_NAME', 'attendance_db'),
            'user' => self::env('DB_USER', 'root'),
            'pass' => self::env('DB_PASS', ''),
            'charset' => self::env('DB_CHARSET', 'utf8mb4'),
        ];
    }
}
