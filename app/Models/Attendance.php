<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'class_id',
        'student_id',
        'date',
        'status',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($attendance) {
            $attendance->notifyParentIfAbsentOrLate();
        });

        static::updated(function ($attendance) {
            if ($attendance->wasChanged('status')) {
                $attendance->notifyParentIfAbsentOrLate();
            }
        });
    }

    public function notifyParentIfAbsentOrLate()
    {
        if (in_array($this->status, ['absent', 'late'])) {
            $statusLabel = ucfirst($this->status);
            $student = $this->student;
            if ($student) {
                \App\Models\Notification::create([
                    'user_id' => $student->id,
                    'title'   => "Attendance Alert: {$statusLabel}",
                    'body'    => ($student->name ?? 'Your child') . " was marked {$this->status} on " . $this->date->format('d M Y') . ".",
                    'type'    => 'alerts',
                ]);
            }
        }
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
