<?php
declare(strict_types=1);

namespace App\Enums;

/** State Machine ของ Ticket ตาม Transition Rules Matrix */
enum TicketStatus: string
{
    case Open = 'Open';
    case Assigned = 'Assigned';
    case InProgress = 'InProgress';
    case Resolved = 'Resolved';
    case Closed = 'Closed';

    /** @return self[] สถานะที่เปลี่ยนไปได้จากสถานะนี้ */
    public function next(): array
    {
        return match ($this) {
            self::Open => [self::Assigned],
            self::Assigned => [self::InProgress],
            self::InProgress => [self::Resolved],
            self::Resolved => [self::Closed, self::InProgress],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->next(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Open => 'เปิดใหม่',
            self::Assigned => 'มอบหมายแล้ว',
            self::InProgress => 'กำลังดำเนินการ',
            self::Resolved => 'รอยืนยันผล',
            self::Closed => 'ปิดงาน',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Open => 'bg-slate-100 text-slate-700',
            self::Assigned => 'bg-indigo-100 text-indigo-700',
            self::InProgress => 'bg-amber-100 text-amber-700',
            self::Resolved => 'bg-sky-100 text-sky-700',
            self::Closed => 'bg-emerald-100 text-emerald-700',
        };
    }
}
