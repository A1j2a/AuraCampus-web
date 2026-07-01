<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'title',
        'content',
        'type',
        'attachment_path',
        'attachment_type',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

}
