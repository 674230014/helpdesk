<?php
declare(strict_types=1);

namespace App\Core;

use App\Enums\UserRole;

/** Middleware Pipeline: auth / role:<name,...> / csrf */
final class Middleware
{
    public static function run(string $spec): void
    {
        [$name, $arg] = array_pad(explode(':', $spec, 2), 2, null);
        match ($name) {
            'auth' => self::auth(),
            'csrf' => self::csrf(),
            'role' => self::role((string) $arg),
            default => throw new \InvalidArgumentException("Unknown middleware $spec"),
        };
    }

    private static function auth(): void
    {
        if (!Auth::check()) {
            if (Response::wantsJson()) {
                Response::json(['ok' => false, 'error' => 'กรุณาเข้าสู่ระบบ'], 401);
            }
            Response::redirect('/login');
        }
    }

    private static function csrf(): void
    {
        if (!Csrf::verify()) {
            Response::abort(419, 'CSRF token ไม่ถูกต้อง กรุณารีเฟรชหน้า');
        }
    }

    private static function role(string $roles): void
    {
        $allowed = array_map(fn ($r) => UserRole::from($r), explode(',', $roles));
        if (!in_array(Auth::role(), $allowed, true)) {
            Response::abort(403, 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');
        }
    }
}
