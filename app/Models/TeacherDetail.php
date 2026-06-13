<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TeacherDetail extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'designation',
        'qualification',
        'experience',
        'joining_date',
        'is_active',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'is_active'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classAssignments(): HasMany
    {
        return $this->hasMany(TeacherClassSection::class, 'teacher_id', 'user_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
