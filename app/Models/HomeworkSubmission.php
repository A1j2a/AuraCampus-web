<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    protected $fillable = [
        'homework_id',
        'student_id',
        'reply_note',
        'files',
        'status',
        'grade',
        'feedback',
        'submitted_at',
        'graded_at',
        'graded_by',
    ];

    protected $casts = [
        'files'        => 'array',
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    protected static function booted()
    {
        static::created(function ($submission) {
            $homework = $submission->homework;
            if ($homework && in_array($submission->status, ['submitted', 'late'])) {
                $studentName = $submission->student?->name ?? 'A student';
                \App\Models\Notification::create([
                    'user_id' => $homework->teacher_id,
                    'title'   => "Homework Submitted: " . $studentName,
                    'body'    => $studentName . " submitted homework for " . $homework->subject->name . " (Title: " . $homework->title . ")",
                    'type'    => 'academic',
                ]);
            }
        });

        static::updated(function ($submission) {
            $homework = $submission->homework;
            if (!$homework) {
                return;
            }

            // Notify teacher on resubmission
            if ($submission->wasChanged('status') && in_array($submission->status, ['submitted', 'late'])) {
                $studentName = $submission->student?->name ?? 'A student';
                \App\Models\Notification::create([
                    'user_id' => $homework->teacher_id,
                    'title'   => "Homework Submitted: " . $studentName,
                    'body'    => $studentName . " submitted homework for " . $homework->subject->name . " (Title: " . $homework->title . ")",
                    'type'    => 'academic',
                ]);
            }

            // Notify student on grading
            if ($submission->wasChanged('status') && in_array($submission->status, ['approved', 'revision_requested'])) {
                \App\Models\Notification::create([
                    'user_id' => $submission->student_id,
                    'title'   => "Homework Graded: " . $homework->title,
                    'body'    => "Your submission was " . str_replace('_', ' ', $submission->status) . ($submission->grade ? " with Grade: " . $submission->grade : ""),
                    'type'    => 'academic',
                ]);
            }
        });
    }
}
