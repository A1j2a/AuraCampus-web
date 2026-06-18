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
}
