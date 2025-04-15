<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schoolyear extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolyear',
        'startDate',
        'endDate',
        'status',
    ];

    protected $casts = [
        'startDate' => 'date',
        'endDate' => 'date',
    ];

    // public function getSchoolYearAttribute()
    // {
    //     return $this->startDate->format('Y').' - '.$this->endDate->format('Y');
    // }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'schoolyear_id');
    }

    public function getTotalCollectionAttribute()
    {
        return $this->semesters->flatMap->collections->sum('amount');
    }

    public function syears(): HasMany
    {
        return $this->hasMany(Syear::class);
    }

    // public function enrollments(): HasMany
    // {
    //     return $this->hasMany(Enrollment::class, 'semester_stud', 'semester_id', 'stud_id');
    // }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'schoolyear_id');
    }
}
