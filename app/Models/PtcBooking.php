<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtcBooking extends Model
{
    use BelongsToSchool;

    protected $table = 'ptc_bookings';

    protected $fillable = [
        'school_id',
        'student_id',
        'parent_id',
        'term',
        'ptc_date',
        'time_slot',
        'status',
    ];

    protected $casts = [
        'ptc_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
