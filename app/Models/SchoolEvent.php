<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEvent extends Model
{
    use BelongsToSchool;

    protected $table = 'school_events';

    protected $fillable = [
        'school_id',
        'title',
        'description',
        'type',
        'event_date',
        'event_time',
        'organizer',
        'organizer_avatar_url',
        'banner_image_url',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($event) {
            try {
                $users = \App\Models\User::where('school_id', $event->school_id)
                    ->whereNotNull('fcm_token')
                    ->whereIn('user_type', [3, 4]) // 3 = Student, 4 = Parent
                    ->get();

                $firebaseService = app(\App\Services\FirebaseService::class);

                foreach ($users as $user) {
                    $firebaseService->sendPush(
                        $user->fcm_token,
                        "New Event: " . $event->title,
                        $event->event_time ? $event->event_time . " - " . $event->description : $event->description,
                        [
                            'id' => (string) $event->id,
                            'type' => 'event'
                        ]
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("FCM event push failed: " . $e->getMessage());
            }
        });
    }
}
