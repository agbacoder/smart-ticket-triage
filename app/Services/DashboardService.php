<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Collection;


class DashboardService
{
    public function getStats(): array
    {
        return [
            'total_tickets' => $this->getTotalTickets(),
            'status'     => $this->getByStatus(),
            'category'   => $this->getByCategory(),
        ];
    }

    private function getTotalTickets(): int
    {
        return Ticket::count();
    }

    private function getByStatus(): Collection
    {
        return Ticket::query()
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    private function getByCategory(): Collection
    {
        return Ticket::query()
            ->whereNotNull('category')
            ->select('category')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');
    }
}
