<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCredential extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'plain_password',
    ];

    protected $hidden = ['plain_password'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
