<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentLeaveRequest extends Model
{
    protected $fillable = [
        'school_id',
        'parent_id',
        'reason',
        'description',
        'attachments',
        'from_date',
        'to_date',
        'status',
        'admin_remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'reviewed_at' => 'datetime',
        'attachments' => 'array',
    ];

    protected static function booted()
    {
        static::updated(function ($leaveRequest) {
            if ($leaveRequest->wasChanged('status')) {
                try {
                    $status = ucfirst($leaveRequest->status);
                    foreach ($leaveRequest->students as $student) {
                        \App\Models\Notification::create([
                            'user_id' => $student->id,
                            'title' => "Leave Request {$status}",
                            'body' => "Leave request for {$student->name} has been {$leaveRequest->status}.",
                            'type' => 'alerts',
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to create notification on leave update: " . $e->getMessage());
                }
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Students included in this leave request.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'leave_request_students', 'leave_request_id', 'student_id')
                    ->withTimestamps();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Number of leave days.
     */
    public function getLeaveDaysAttribute(): int
    {
        return (int) $this->from_date->diffInDays($this->to_date) + 1;
    }
}
