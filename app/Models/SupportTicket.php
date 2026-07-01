<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'subject',
        'description',
        'priority',
        'status',
    ];

    protected static function booted()
    {
        static::updated(function ($ticket) {
            if ($ticket->wasChanged('status')) {
                try {
                    \App\Models\Notification::create([
                        'user_id' => $ticket->user_id,
                        'title'   => "Support Ticket Status Updated",
                        'body'    => "Your support ticket '{$ticket->subject}' status is now: " . ucfirst($ticket->status) . ".",
                        'type'    => 'general',
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to notify on ticket status update: " . $e->getMessage());
                }
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'support_ticket_id');
    }
}
