<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEvent extends Model
{
    use BelongsToSchool;

    protected $table = 'school_events';

    protected $fillable = [
        'school_id',
        'title',
        'description',
        'type',
        'event_date',
        'event_time',
        'organizer',
        'organizer_avatar_url',
        'banner_image_url',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];
}
