<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolyear_id',
        'semester',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(Schoolyear::class, 'schoolyear_id');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class, 'semester_id');
    }

    public function getSemesterTotalCollectionAttribute()
    {
        return $this->collections->sum('amount');
    }

    public function syears(): BelongsToMany
    {
        return $this->belongsToMany(Syear::class, 'semester_syear', 'semester_id', 'syear_id')
            ->withTimestamps(); // Optional, if your pivot table has timestamps
    }

    public function enrollments(): BelongsToMany
    {
        return $this->belongsToMany(Enrollment::class)
            ->withTimestamps();
    }
}
