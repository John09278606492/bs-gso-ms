<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'program',
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function yearlevels(): HasMany
    {
        return $this->hasMany(Yearlevel::class, 'program_id');
    }

    public function scolleges(): HasMany
    {
        return $this->hasMany(Scollege::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'program_id');
    }
}
