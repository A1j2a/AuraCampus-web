<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    /**
     * List all support tickets.
     */
    public function index(): View
    {
        $tickets = SupportTicket::with(['school', 'user'])
            ->latest()
            ->get();

        return view('superadmin.support.index', compact('tickets'));
    }

    /**
     * Show ticket details and chat thread.
     */
    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['messages.user', 'user', 'school']);
        return view('superadmin.support.show', compact('ticket'));
    }

    /**
     * Update ticket properties (status).
     */
    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:open,pending,closed',
        ]);

        $ticket->update([
            'status' => $request->status,
        ]);

        return redirect()->route('superadmin.support.show', $ticket)
            ->with('success', 'Ticket status updated successfully!');
    }

    /**
     * Post a response message.
     */
    public function message(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Keep status open or move to pending upon reply
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'pending']);
        }

        return redirect()->route('superadmin.support.show', $ticket)
            ->with('success', 'Reply posted successfully!');
    }
}

