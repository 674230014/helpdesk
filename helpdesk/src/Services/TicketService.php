<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\EventDispatcher;
use App\Enums\TicketStatus;
use App\Repositories\CommentRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\TicketRepository;

final class TicketService
{
    public function __construct(
        private TicketRepository $tickets,
        private StatusLogRepository $logs,
        private CommentRepository $comments,
        private FileUploader $uploader,
        private EventDispatcher $events,
        private Database $db
    ) {
    }

    /** Sequence 6.1: upload → insert ticket → log → dispatch ticket.created */
    public function createTicket(array $data, ?array $file, int $userId): array
    {
        $image = $this->uploader->upload($file);
        $now = date('Y-m-d H:i:s');

        $this->db->beginTransaction();
        try {
            $id = $this->tickets->create([
                'user_id' => $userId,
                'category_id' => (int) $data['category_id'],
                'title' => trim($data['title']),
                'description' => trim($data['description']),
                'status' => TicketStatus::Open->value,
                'priority' => $data['priority'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->logs->create([
                'ticket_id' => $id, 'changed_by' => $userId,
                'from_status' => null, 'to_status' => TicketStatus::Open->value,
                'note' => 'สร้าง Ticket', 'created_at' => $now,
            ]);
            if ($image) {
                $this->comments->create([
                    'ticket_id' => $id, 'user_id' => $userId,
                    'body' => 'แนบรูปประกอบปัญหา', 'image_path' => $image, 'created_at' => $now,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $ticket = $this->tickets->find($id);
        $this->events->dispatch('ticket.created', ['ticket' => $ticket]);
        return $ticket;
    }

    public function addComment(int $ticketId, int $userId, string $body, ?array $file): int
    {
        $image = $this->uploader->upload($file);
        return $this->comments->create([
            'ticket_id' => $ticketId, 'user_id' => $userId, 'body' => trim($body),
            'image_path' => $image, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
