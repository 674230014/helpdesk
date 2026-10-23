<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $to): never
    {
        header('Location: ' . $to);
        exit;
    }

    public static function abort(int $code, string $message = ''): never
    {
        if (self::wantsJson()) {
            self::json(['ok' => false, 'error' => $message ?: 'Error ' . $code], $code);
        }
        http_response_code($code);
        View::render('error', ['code' => $code, 'message' => $message], 'layouts/main');
        exit;
    }

    public static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        return str_contains($accept, 'application/json') || str_starts_with($uri, '/api/');
    }
}
