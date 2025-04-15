<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Syear extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'schoolyear_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolyear(): BelongsTo
    {
        return $this->belongsTo(Schoolyear::class);
    }

    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'syear_semester', 'syear_id', 'semester_id')
            ->withTimestamps(); // Optional, if your pivot table has timestamps
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_syear', 'syear_id', 'collection_id')
            ->withTimestamps();
    }

    public function getTotalAmountAttribute()
    {
        if ($this->collections->isEmpty()) {
            return '₱0.00'; // Return a default value if no yearlevelpayments are associated
        }

        $totalAmount = $this->collections->sum('amount'); // Sum of all 'amount' in related yearlevelpayments

        return '₱'.number_format($totalAmount, 2);
    }
}
