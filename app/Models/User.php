<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'user_name',
        'user_type',
        'email',
        'password',
        'school_id',
        'device_info',
        'device_type',
        'device_os_version',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the school this user belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get teacher details (if user is a teacher).
     */
    public function teacherDetail(): HasOne
    {
        return $this->hasOne(TeacherDetail::class);
    }

    /**
     * Get student details (if user is a student).
     */
    public function studentDetail(): HasOne
    {
        return $this->hasOne(StudentDetail::class);
    }

    /**
     * Get children associated with this parent user.
     */
    public function children(): HasMany
    {
        return $this->hasMany(StudentDetail::class, 'user_id')
                    ->join('parent_student', 'student_details.user_id', '=', 'parent_student.student_id')
                    ->where('parent_student.parent_id', $this->id);
    }

    /**
     * Get students associated with this parent user (via pivot).
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    /**
     * Get active academic session for user's school.
     */
    public function getActiveAcademicSession()
    {
        $schoolId = $this->school_id;
        
        // Check session safely
        if (function_exists('session') && request()->hasSession()) {
            $sessionId = session('active_academic_session_id');
            if ($sessionId) {
                $session = \App\Models\AcademicSession::where('school_id', $schoolId)->find($sessionId);
                if ($session) return $session;
            }
        }

        // Fallback to primary active session
        $active = \App\Models\AcademicSession::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
        if ($active) return $active;

        // Fallback to latest session
        return \App\Models\AcademicSession::where('school_id', $schoolId)
            ->latest()
            ->first();
    }
}
