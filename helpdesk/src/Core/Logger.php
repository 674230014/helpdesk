<?php
declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function info(string $msg): void
    {
        self::write('INFO', $msg);
    }

    public static function error(string $msg): void
    {
        self::write('ERROR', $msg);
    }

    private static function write(string $level, string $msg): void
    {
        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $level, $msg);
        @file_put_contents(BASE_PATH . '/storage/logs/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
