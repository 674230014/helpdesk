<?php
declare(strict_types=1);

namespace App\Repositories;

final class TicketRepository extends AbstractRepository
{
    protected string $table = 'tickets';
    protected array $columns = [
        'user_id', 'category_id', 'technician_id', 'title', 'description', 'status', 'priority',
        'created_at', 'updated_at', 'resolved_at', 'closed_at',
    ];

    private const SELECT = 'SELECT t.*, c.name AS category_name, u.name AS requester_name, tech.name AS technician_name
        FROM tickets t
        JOIN categories c ON c.id = t.category_id
        JOIN users u ON u.id = t.user_id
        LEFT JOIN users tech ON tech.id = t.technician_id';

    public function findDetailed(int $id): ?array
    {
        $row = $this->db->query(self::SELECT . ' WHERE t.id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public function findByUser(int $userId): array
    {
        return $this->search('', ['user_id' => $userId]);
    }

    public function findByTechnician(int $techId): array
    {
        return $this->search('', ['technician_id' => $techId]);
    }

    /** filters: user_id, technician_id, status, priority */
    public function search(string $keyword = '', array $filters = []): array
    {
        $where = [];
        $params = [];
        if ($keyword !== '') {
            $where[] = '(t.title LIKE ? OR t.description LIKE ?)';
            $params[] = $params[] = '%' . $keyword . '%';
        }
        foreach (['user_id', 'technician_id', 'status', 'priority'] as $f) {
            if (!empty($filters[$f])) {
                $where[] = "t.$f = ?";
                $params[] = $filters[$f];
            }
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY t.id DESC LIMIT 200';
        return $this->db->query($sql, $params)->fetchAll();
    }

    // ---- สถิติสำหรับ Dashboard ----

    /** @return array<string,int> */
    public function countByStatus(): array
    {
        $rows = $this->db->query('SELECT status, COUNT(*) AS n FROM tickets GROUP BY status')->fetchAll();
        return array_column($rows, 'n', 'status');
    }

    /** เวลาแก้ไขเฉลี่ย (ชั่วโมง) จาก created_at → resolved_at */
    public function avgResolutionTime(): float
    {
        $rows = $this->db->query('SELECT created_at, resolved_at FROM tickets WHERE resolved_at IS NOT NULL')->fetchAll();
        if (!$rows) {
            return 0.0;
        }
        $sum = 0;
        foreach ($rows as $r) {
            $sum += strtotime($r['resolved_at']) - strtotime($r['created_at']);
        }
        return round($sum / count($rows) / 3600, 1);
    }

    public function topTechnicians(int $limit = 5): array
    {
        return $this->db->query(
            "SELECT u.name, COUNT(*) AS n FROM tickets t
             JOIN users u ON u.id = t.technician_id
             WHERE t.status IN ('Resolved','Closed')
             GROUP BY u.id, u.name ORDER BY n DESC LIMIT " . (int) $limit
        )->fetchAll();
    }

    public function monthlyRatings(): array
    {
        $month = $this->db->driver() === 'mysql' ? "DATE_FORMAT(created_at, '%Y-%m')" : "strftime('%Y-%m', created_at)";
        return $this->db->query(
            "SELECT $month AS month, ROUND(AVG(score), 2) AS avg_score, COUNT(*) AS n
             FROM ratings GROUP BY month ORDER BY month DESC LIMIT 6"
        )->fetchAll();
    }
}
