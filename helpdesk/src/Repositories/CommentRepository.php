<?php
declare(strict_types=1);

namespace App\Repositories;

final class CommentRepository extends AbstractRepository
{
    protected string $table = 'comments';
    protected array $columns = ['ticket_id', 'user_id', 'body', 'image_path', 'created_at'];

    public function forTicket(int $ticketId): array
    {
        return $this->db->query(
            'SELECT c.*, u.name AS author_name, u.role AS author_role
             FROM comments c JOIN users u ON u.id = c.user_id
             WHERE c.ticket_id = ? ORDER BY c.id',
            [$ticketId]
        )->fetchAll();
    }
}
