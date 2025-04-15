<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    use HasFactory;

    protected $fillable = [
        'college',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'college_id');
    }

    public function scolleges(): HasMany
    {
        return $this->hasMany(Scollege::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'college_id');
    }
}
