<?php

namespace App\Traits;

use App\Models\Scopes\SchoolScope;
use App\Models\School;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    /**
     * Boot the BelongsToSchool trait.
     */
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope);

        // Automatically associate with authenticated user's school on creation
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->school_id !== null && !isset($model->school_id)) {
                $model->school_id = auth()->user()->school_id;
            }
        });
    }

    /**
     * Get the school that owns this model.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
