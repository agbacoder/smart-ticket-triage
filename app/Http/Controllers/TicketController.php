<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use Illuminate\Http\Request;
use App\Jobs\ClassifyTicket;


class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $tickets = $this->ticketService->list($request->all(), $request->get('per_page', 10));

        return response()->json([
            'status'  => 200,
            'data'    => $tickets,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $ticket = $this->ticketService->create($data);

        return response()->json([
            'status'  => 201,
            'data'    => $ticket,
        ], 201);
    }

    public function show(string $id)
    {
        $ticket = $this->ticketService->find($id);

        return response()->json([
            'status'  => 200,
            'data'    => $ticket,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $ticket = $this->ticketService->find($id);

        $data = $request->validate([
            'status'     => 'nullable|in:open,in_progress,closed',
            'category'   => 'nullable|string',
            'note'       => 'nullable|string',
        ]);

        $ticket = $this->ticketService->update($ticket, $data);

        return response()->json([
            'status'  => 200,
            'data'    => $ticket,
        ], 200);
    }


    public function classify(string $id)
    {
        $ticket = $this->ticketService->find($id);

        ClassifyTicket::dispatch($ticket->id);

        return response()->json([
            'status'  => 202,
            'message' => 'Classification job queued',
            'data'    => [
                'ticket_id' => $ticket->id,
                'queued'    => true,
            ],
        ], 202);
    }

}
