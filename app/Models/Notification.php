<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public $dont_notify_parents = false;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'image_url',
        'document_url',
        'type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($notification) {
            $user = $notification->user;
            if (!$user) {
                return;
            }

            $firebaseService = app(\App\Services\FirebaseService::class);
            $sentTokens = [];

            // 1. Send to the user themselves (if they have a token)
            if ($user->fcm_token) {
                try {
                    $firebaseService->sendPush(
                        $user->fcm_token,
                        $notification->title,
                        $notification->body,
                        [
                            'id' => (string) $notification->id,
                            'type' => (string) $notification->type,
                            'image_url' => $notification->image_url ? url('storage/' . $notification->image_url) : null,
                            'document_url' => $notification->document_url ? url('storage/' . $notification->document_url) : null,
                        ]
                    );
                    $sentTokens[] = $user->fcm_token;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("FCM failed to send to user: " . $e->getMessage());
                }
            }

            // 2. If the user is a student, also send to their parents
            if ($user->user_type == 3 && empty($notification->dont_notify_parents)) { // 3 = Student
                foreach ($user->parents as $parent) {
                    if ($parent->fcm_token && !in_array($parent->fcm_token, $sentTokens)) {
                        try {
                            $firebaseService->sendPush(
                                $parent->fcm_token,
                                $notification->title,
                                $notification->body,
                                [
                                    'id' => (string) $notification->id,
                                    'type' => (string) $notification->type,
                                    'image_url' => $notification->image_url ? url('storage/' . $notification->image_url) : null,
                                    'document_url' => $notification->document_url ? url('storage/' . $notification->document_url) : null,
                                ]
                            );
                            $sentTokens[] = $parent->fcm_token;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("FCM failed to send to parent: " . $e->getMessage());
                        }
                    }
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
