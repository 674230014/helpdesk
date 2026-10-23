<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\TicketStatus;
use App\Repositories\TicketRepository;

final class DashboardService
{
    public function __construct(private TicketRepository $tickets)
    {
    }

    public function getStatistics(): array
    {
        $counts = $this->tickets->countByStatus();
        $byStatus = [];
        foreach (TicketStatus::cases() as $s) {
            $byStatus[$s->value] = (int) ($counts[$s->value] ?? 0);
        }
        return [
            'byStatus' => $byStatus,
            'total' => array_sum($byStatus),
            'avgResolutionHours' => $this->tickets->avgResolutionTime(),
            'topTechnicians' => $this->tickets->topTechnicians(),
            'monthlyRatings' => $this->tickets->monthlyRatings(),
        ];
    }
}
