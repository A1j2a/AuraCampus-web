<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'max_marks',
        'passing_marks',
    ];

    protected static function booted()
    {
        static::created(function ($schedule) {
            try {
                $exam = $schedule->exam;
                $subject = $schedule->subject;
                if ($exam && $subject) {
                    $studentUserIds = \App\Models\StudentDetail::where('class_id', $schedule->class_id)
                        ->pluck('user_id');

                    foreach ($studentUserIds as $studentUserId) {
                        \App\Models\Notification::create([
                            'user_id' => $studentUserId,
                            'title'   => "New Exam Scheduled: " . $exam->name,
                            'body'    => $subject->name . " exam is scheduled for " . \Carbon\Carbon::parse($schedule->exam_date)->format('d M Y') . " (" . \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') . ").",
                            'type'    => 'academic',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to notify on exam schedule creation: " . $e->getMessage());
            }
        });
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}
