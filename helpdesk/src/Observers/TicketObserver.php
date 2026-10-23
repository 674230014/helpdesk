<?php
declare(strict_types=1);

namespace App\Observers;

use App\Core\EventDispatcher;
use App\Enums\TicketStatus;
use App\Notifications\NotificationChannelInterface;
use App\Repositories\UserRepository;

/** รับ event ticket.created / ticket.status_changed แล้วแจ้งเตือนผู้เกี่ยวข้อง */
final class TicketObserver
{
    public function __construct(
        private NotificationChannelInterface $notifier,
        private UserRepository $users,
        EventDispatcher $events
    ) {
        $events->listen('ticket.created', fn (array $p) => $this->handleCreated($p['ticket']));
        $events->listen('ticket.status_changed', fn (array $p) => $this->handleStatusChanged($p['ticket'], $p['from'], $p['to']));
    }

    public function handleCreated(array $ticket): void
    {
        $msg = "🆕 Ticket #{$ticket['id']} ใหม่: {$ticket['title']} (ความสำคัญ: {$ticket['priority']})";
        foreach ($this->users->byRole('admin') as $admin) {
            $this->notifier->send($msg, $admin['line_user_id'] ?: null);
        }
    }

    public function handleStatusChanged(array $ticket, TicketStatus $from, TicketStatus $to): void
    {
        $base = "Ticket #{$ticket['id']} ({$ticket['title']})";
        $techLine = $this->lineOf($ticket['technician_id'] ?? null);
        $ownerLine = $this->lineOf($ticket['user_id'] ?? null);

        match ($to) {
            TicketStatus::Assigned => $this->notifier->send("📌 $base ถูกมอบหมายให้คุณ", $techLine),
            TicketStatus::InProgress => $from === TicketStatus::Resolved
                ? $this->notifier->send("↩️ $base ถูกส่งกลับให้แก้ไขใหม่", $techLine)
                : $this->notifier->send("🔧 $base ช่างรับงานแล้ว กำลังดำเนินการ", $ownerLine),
            TicketStatus::Resolved => $this->notifier->send("✅ $base ซ่อมเสร็จแล้ว กรุณาตรวจสอบและยืนยัน", $ownerLine),
            TicketStatus::Closed => $this->notifier->send("🎉 $base ปิดงานเรียบร้อย", $techLine),
            default => null,
        };
    }

    private function lineOf(null|int|string $userId): ?string
    {
        if (!$userId) {
            return null;
        }
        $u = $this->users->find((int) $userId);
        return $u['line_user_id'] ?? null ?: null;
    }
}
