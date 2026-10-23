<?php
declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends AbstractRepository
{
    protected string $table = 'users';
    protected array $columns = ['name', 'email', 'password_hash', 'role', 'line_user_id', 'created_at'];

    public function findByEmail(string $email): ?array
    {
        $row = $this->db->query('SELECT * FROM users WHERE email = ?', [$email])->fetch();
        return $row ?: null;
    }

    public function byRole(string $role): array
    {
        return $this->db->query('SELECT id, name, email, line_user_id FROM users WHERE role = ? ORDER BY name', [$role])->fetchAll();
    }
}
