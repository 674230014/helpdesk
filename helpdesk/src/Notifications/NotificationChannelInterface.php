<?php
declare(strict_types=1);

namespace App\Notifications;

/** Strategy Pattern: เพิ่ม Email/Slack ได้โดยไม่แก้ Observer */
interface NotificationChannelInterface
{
    public function send(string $message, ?string $to = null): bool;
}
