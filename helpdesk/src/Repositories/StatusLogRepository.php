<?php
declare(strict_types=1);

namespace App\Repositories;

/** Audit log ของการเปลี่ยนสถานะ (ตาราง status_logs) */
final class StatusLogRepository extends AbstractRepository
{
    protected string $table = 'status_logs';
    protected array $columns = ['ticket_id', 'changed_by', 'from_status', 'to_status', 'note', 'created_at'];

    public function forTicket(int $ticketId): array
    {
        return $this->db->query(
            'SELECT s.*, u.name AS actor_name FROM status_logs s
             JOIN users u ON u.id = s.changed_by
             WHERE s.ticket_id = ? ORDER BY s.id',
            [$ticketId]
        )->fetchAll();
    }
}
