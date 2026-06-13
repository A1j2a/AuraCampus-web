<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'status',
        'logo_path',
        'font_family',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function academicSessions(): HasMany
    {
        return $this->hasMany(AcademicSession::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'school_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }
}
