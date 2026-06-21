<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    use BelongsToSchool;

    protected $table = 'homeworks';

    protected $fillable = [
        'school_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'max_marks',
        'attachments',
        'published_at',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'published_at' => 'datetime',
        'attachments'  => 'array',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    protected static function booted()
    {
        static::created(function ($homework) {
            if ($homework->status === 'published') {
                $homework->notifyStudents();
            }
        });

        static::updated(function ($homework) {
            if ($homework->wasChanged('status') && $homework->status === 'published') {
                $homework->notifyStudents();
            }
        });
    }

    public function notifyStudents()
    {
        $studentUserIds = \App\Models\StudentDetail::where('class_id', $this->class_id)
            ->pluck('user_id');

        foreach ($studentUserIds as $studentUserId) {
            \App\Models\Notification::create([
                'user_id' => $studentUserId,
                'title'   => "New Homework: " . $this->title,
                'body'    => "A new homework has been assigned for " . $this->subject->name . ". Due: " . ($this->due_date ? $this->due_date->format('d M Y') : 'N/A'),
                'type'    => 'academic',
            ]);
        }
    }
}
