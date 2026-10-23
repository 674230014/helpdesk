<?php
declare(strict_types=1);

namespace App\Core;

use App\Enums\UserRole;

/** Session-based authentication */
final class Auth
{
    public static function login(string $email, string $password): bool
    {
        $stmt = Database::getInstance()->query('SELECT * FROM users WHERE email = ?', [$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): array
    {
        return $_SESSION['user'] ?? [];
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    public static function role(): ?UserRole
    {
        return isset($_SESSION['user']) ? UserRole::from($_SESSION['user']['role']) : null;
    }
}
