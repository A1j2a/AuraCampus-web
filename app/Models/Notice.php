<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'title',
        'content',
        'type',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($notice) {
            try {
                $users = \App\Models\User::where('school_id', $notice->school_id)
                    ->whereNotNull('fcm_token')
                    ->whereIn('user_type', [3, 4]) // 3 = Student, 4 = Parent
                    ->get();

                $firebaseService = app(\App\Services\FirebaseService::class);

                foreach ($users as $user) {
                    $firebaseService->sendPush(
                        $user->fcm_token,
                        "Notice: " . $notice->title,
                        $notice->content,
                        [
                            'id' => (string) $notice->id,
                            'type' => 'notice'
                        ]
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("FCM notice push failed: " . $e->getMessage());
            }
        });
    }
}
