<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
    ];

    protected static function booted()
    {
        static::created(function ($message) {
            try {
                $ticket = $message->ticket;
                // If message is from someone else, notify the ticket creator
                if ($ticket && $message->user_id !== $ticket->user_id) {
                    \App\Models\Notification::create([
                        'user_id' => $ticket->user_id,
                        'title'   => "New Reply on Support Ticket",
                        'body'    => "You have a new reply on your ticket '{$ticket->subject}': " . substr($message->message, 0, 50) . "...",
                        'type'    => 'general',
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to notify on support ticket reply: " . $e->getMessage());
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
