<?php
declare(strict_types=1);

namespace App\Notifications;

use App\Core\Env;
use App\Core\Logger;

/** ส่งข้อความผ่าน LINE Messaging API (push) — ถ้าไม่ตั้งค่า token จะบันทึก log แทน (dry-run) */
final class LineMessagingService implements NotificationChannelInterface
{
    private string $channelToken;

    public function __construct()
    {
        $this->channelToken = (string) Env::get('LINE_CHANNEL_TOKEN', '');
    }

    public function send(string $message, ?string $to = null): bool
    {
        if ($this->channelToken === '' || !$to) {
            Logger::info('[LINE dry-run] to=' . ($to ?? '-') . ' | ' . $message);
            return true;
        }
        return $this->callApi('https://api.line.me/v2/bot/message/push', [
            'to' => $to,
            'messages' => [['type' => 'text', 'text' => mb_substr($message, 0, 4900)]],
        ]);
    }

    private function callApi(string $endpoint, array $body): bool
    {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channelToken,
            ],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            Logger::error("LINE API responded HTTP $code");
        }
        return $code === 200;
    }
}
