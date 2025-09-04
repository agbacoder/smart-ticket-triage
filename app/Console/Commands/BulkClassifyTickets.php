<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Jobs\ClassifyTicket;

class BulkClassifyTickets extends Command
{
    protected $signature = 'tickets:bulk-classify {--limit=10 : Number of tickets to classify}';
    protected $description = 'Dispatch classification jobs for multiple unclassified tickets';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $tickets = Ticket::whereNull('category')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('No unclassified tickets found.');
            return self::SUCCESS;
        }

        foreach ($tickets as $ticket) {
            ClassifyTicket::dispatch($ticket->id);
            $this->info("Dispatched classification job for Ticket ID: {$ticket->id}");
        }

        return self::SUCCESS;
    }
}
