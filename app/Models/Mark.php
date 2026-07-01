<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'exam_schedule_id',
        'student_id',
        'marks_obtained',
        'grade',
        'remarks',
        'entered_by',
    ];

    protected static function booted()
    {
        static::created(function ($mark) {
            $mark->notifyStudentAndParent();
        });

        static::updated(function ($mark) {
            if ($mark->wasChanged('marks_obtained') || $mark->wasChanged('grade')) {
                $mark->notifyStudentAndParent();
            }
        });
    }

    public function notifyStudentAndParent()
    {
        try {
            $schedule = $this->examSchedule;
            $examName = $schedule?->exam?->name ?? 'Exam';
            $subjectName = $schedule?->subject?->name ?? 'Subject';
            
            \App\Models\Notification::create([
                'user_id' => $this->student_id,
                'title'   => "Marks Released: {$examName}",
                'body'    => "Your marks for {$subjectName} have been published. Marks obtained: {$this->marks_obtained}/{$schedule->max_marks} (Grade: {$this->grade}).",
                'type' => 'academic',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to notify student/parent on marks release: " . $e->getMessage());
        }
    }

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
