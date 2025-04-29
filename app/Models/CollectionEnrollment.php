<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionEnrollment extends Model
{
    use HasFactory;

    protected $table = 'collection_enrollment';

    public $timestamps = true;

    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    // Optionally, if you want to easily access the student
    public function student()
    {
        return $this->enrollment?->stud()->getFullNameAttribute(); // Only works if Enrollment has a `student()` relationship
    }
}
