<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    /**
     * List school support tickets.
     */
    public function index(): View
    {
        $tickets = SupportTicket::where('school_id', auth()->user()->school_id)
            ->latest()
            ->get();

        return view('school.support.index', compact('tickets'));
    }

    /**
     * Submit a new support ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = SupportTicket::create([
            'school_id' => auth()->user()->school_id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        return redirect()->route('school.support.show', $ticket)
            ->with('success', 'Support ticket opened successfully!');
    }

    /**
     * View discussion thread.
     */
    public function show(SupportTicket $ticket): View
    {
        // Tenant Isolation
        if ($ticket->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized.');
        }

        $ticket->load(['messages.user', 'user']);

        return view('school.support.show', compact('ticket'));
    }

    /**
     * Post a reply message.
     */
    public function message(Request $request, SupportTicket $ticket): RedirectResponse
    {
        // Tenant Isolation
        if ($ticket->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Reopen ticket if closed
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return redirect()->route('school.support.show', $ticket)
            ->with('success', 'Message sent successfully!');
    }
}
