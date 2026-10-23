<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\EventDispatcher;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Repositories\CommentRepository;
use App\Repositories\RatingRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;

/**
 * Business rules ของ State Machine
 * transition(): validateTransition → checkPermission → required action → TRANSACTION → dispatch
 */
final class TicketStatusService
{
    public function __construct(
        private TicketRepository $tickets,
        private CommentRepository $comments,
        private StatusLogRepository $logs,
        private RatingRepository $ratings,
        private UserRepository $users,
        private FileUploader $uploader,
        private EventDispatcher $events,
        private Database $db
    ) {
    }

    /**
     * @param array $actor  ['id'=>int,'role'=>string]
     * @param array $input  technician_id | comment | score, feedback | reason
     * @throws \DomainException เมื่อผิดกฎ (แปลงเป็น HTTP 422/403 ที่ controller)
     */
    public function transition(array $ticket, TicketStatus $to, array $actor, array $input = [], ?array $file = null): array
    {
        $from = TicketStatus::from($ticket['status']);
        $this->validateTransition($from, $to);
        $this->checkPermission($ticket, $from, $to, $actor);

        $now = date('Y-m-d H:i:s');
        $update = ['status' => $to->value, 'updated_at' => $now];
        $commentBody = null;
        $image = null;
        $note = null;

        // ---- Required Action ตาม Transition Rules Matrix ----
        switch ([$from, $to]) {
            case [TicketStatus::Open, TicketStatus::Assigned]:
                $tech = $this->users->find((int) ($input['technician_id'] ?? 0));
                if (!$tech || $tech['role'] !== UserRole::Technician->value) {
                    throw new \DomainException('กรุณาเลือกช่างเทคนิค (technician_id)');
                }
                $update['technician_id'] = (int) $tech['id'];
                $note = 'มอบหมายให้ ' . $tech['name'];
                break;

            case [TicketStatus::Assigned, TicketStatus::InProgress]:
                $commentBody = trim((string) ($input['comment'] ?? ''));
                if ($commentBody === '') {
                    throw new \DomainException('ช่างต้องเพิ่ม comment ตอนรับงาน');
                }
                break;

            case [TicketStatus::InProgress, TicketStatus::Resolved]:
                $image = $this->uploader->upload($file);
                if (!$image) {
                    throw new \DomainException('ต้องแนบรูปภาพผลการซ่อมก่อนปิดงานซ่อม');
                }
                $commentBody = trim((string) ($input['comment'] ?? '')) ?: 'ซ่อมเสร็จแล้ว';
                $update['resolved_at'] = $now;
                break;

            case [TicketStatus::Resolved, TicketStatus::Closed]:
                $score = (int) ($input['score'] ?? 0);
                if ($score < 1 || $score > 5) {
                    throw new \DomainException('กรุณาให้คะแนน 1-5');
                }
                $update['closed_at'] = $now;
                break;

            case [TicketStatus::Resolved, TicketStatus::InProgress]:
                $reason = trim((string) ($input['reason'] ?? ''));
                if ($reason === '') {
                    throw new \DomainException('กรุณาระบุเหตุผลที่ไม่ยอมรับผลงาน');
                }
                $commentBody = 'ไม่ยอมรับผล: ' . $reason;
                $update['resolved_at'] = null;
                $note = $reason;
                break;
        }

        $this->db->beginTransaction();
        try {
            $this->tickets->update((int) $ticket['id'], $update);
            if ($commentBody !== null) {
                $this->comments->create([
                    'ticket_id' => $ticket['id'], 'user_id' => $actor['id'],
                    'body' => $commentBody, 'image_path' => $image, 'created_at' => $now,
                ]);
            }
            if ($to === TicketStatus::Closed) {
                $this->ratings->create([
                    'ticket_id' => $ticket['id'], 'score' => (int) $input['score'],
                    'feedback' => trim((string) ($input['feedback'] ?? '')), 'created_at' => $now,
                ]);
            }
            $this->logs->create([
                'ticket_id' => $ticket['id'], 'changed_by' => $actor['id'],
                'from_status' => $from->value, 'to_status' => $to->value,
                'note' => $note ?? $commentBody, 'created_at' => $now,
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $fresh = $this->tickets->find((int) $ticket['id']);
        $this->events->dispatch('ticket.status_changed', ['ticket' => $fresh, 'from' => $from, 'to' => $to]);
        return $fresh;
    }

    public function validateTransition(TicketStatus $from, TicketStatus $to): void
    {
        if (!$from->canTransitionTo($to)) {
            throw new \DomainException("ไม่สามารถเปลี่ยนสถานะจาก {$from->value} เป็น {$to->value} ได้");
        }
    }

    public function checkPermission(array $ticket, TicketStatus $from, TicketStatus $to, array $actor): void
    {
        $role = UserRole::from($actor['role']);
        $isOwner = (int) $ticket['user_id'] === (int) $actor['id'];
        $isAssignedTech = $role === UserRole::Technician && (int) ($ticket['technician_id'] ?? 0) === (int) $actor['id'];

        $ok = match ($to) {
            TicketStatus::Assigned => $role === UserRole::Admin,
            TicketStatus::InProgress => $from === TicketStatus::Assigned ? $isAssignedTech : $isOwner,
            TicketStatus::Resolved => $isAssignedTech,
            TicketStatus::Closed => $isOwner,
            default => false,
        };
        if (!$ok) {
            throw new \DomainException('คุณไม่มีสิทธิ์เปลี่ยนสถานะนี้', 403);
        }
    }
}
