<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Exceptions\ApiException;

class TicketService
{
    public function create(array $data): Ticket
    {
        $ticket = Ticket::create($data);

        if (! $ticket) {
            throw new ApiException('Failed to create ticket', 400);
        }

        return $ticket;
    } 

    public function list(array $filters = [], int $perPage = 10)
    {
        $query = Ticket::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) =>
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
            );
        }

        return $query->paginate($perPage);
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        if (! $ticket->update($data)) {
            throw new ApiException('Failed to update ticket', 400);
        }

        return $ticket;
    }

    public function find(string $id): Ticket
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            throw new ApiException('Ticket not found', 404);
        }
        return $ticket;
        
    }
}
