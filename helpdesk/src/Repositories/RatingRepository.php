<?php
declare(strict_types=1);

namespace App\Repositories;

final class RatingRepository extends AbstractRepository
{
    protected string $table = 'ratings';
    protected array $columns = ['ticket_id', 'score', 'feedback', 'created_at'];

    public function findByTicket(int $ticketId): ?array
    {
        $row = $this->db->query('SELECT * FROM ratings WHERE ticket_id = ?', [$ticketId])->fetch();
        return $row ?: null;
    }
}
