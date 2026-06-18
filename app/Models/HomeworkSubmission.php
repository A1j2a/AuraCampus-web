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
}
