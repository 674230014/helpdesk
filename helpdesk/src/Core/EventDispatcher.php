<?php
declare(strict_types=1);

namespace App\Core;

/** Observer Pattern: listen(event, handler) / dispatch(event, payload) */
final class EventDispatcher
{
    /** @var array<string, callable[]> */
    private array $listeners = [];

    public function listen(string $event, callable $handler): void
    {
        $this->listeners[$event][] = $handler;
    }

    public function dispatch(string $event, array $payload = []): void
    {
        foreach ($this->listeners[$event] ?? [] as $handler) {
            try {
                $handler($payload);
            } catch (\Throwable $e) {
                // ความล้มเหลวของ observer (เช่น LINE ล่ม) ต้องไม่ทำให้ธุรกรรมหลักพัง
                Logger::error('Listener failed for ' . $event . ': ' . $e->getMessage());
            }
        }
    }
}
