<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/** CRUD พื้นฐาน — ชื่อตาราง/คอลัมน์มาจากค่าคงที่ใน subclass เท่านั้น (ไม่รับจาก input) */
abstract class AbstractRepository implements RepositoryInterface
{
    protected string $table;
    /** @var string[] whitelist ของคอลัมน์ที่เขียนได้ */
    protected array $columns = [];

    public function __construct(protected Database $db)
    {
    }

    public function find(int $id): ?array
    {
        $row = $this->db->query("SELECT * FROM {$this->table} WHERE id = ?", [$id])->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY id")->fetchAll();
    }

    public function create(array $data): int
    {
        $data = array_intersect_key($data, array_flip($this->columns));
        $cols = implode(', ', array_keys($data));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        $this->db->query("INSERT INTO {$this->table} ($cols) VALUES ($marks)", array_values($data));
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = array_intersect_key($data, array_flip($this->columns));
        if (!$data) {
            return false;
        }
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($data)));
        $stmt = $this->db->query("UPDATE {$this->table} SET $set WHERE id = ?", [...array_values($data), $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id])->rowCount() > 0;
    }
}
