<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifierService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Facade as Sentry;

class ClassifyTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $ticketId;

    public function __construct(string $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function handle()
    {
        try {
            $ticket = Ticket::findOrFail($this->ticketId);

            $classifier = app(TicketClassifierService::class);
            $result = $classifier->classify($ticket);

            $ticket->update([
                'category'    => $ticket->category ?? $result['category'],
                'explanation' => $result['explanation'],
                'confidence'  => $result['confidence'],
            ]);

            Log::info('Ticket classified successfully', [
                'ticket_id'  => $ticket->id,
                'category'   => $ticket->category,
                'confidence' => $result['confidence'],
            ]);

        } catch (\Throwable $e) {
            Log::error('Ticket classification failed', [
                'ticket_id' => $this->ticketId,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            Sentry::captureException($e);

            throw $e;
        }
    }
}
