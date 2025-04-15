<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Yearlevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'yearlevel',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Get all of the comments for the Yearlevel
     */
    public function yearlevelpayments(): HasMany
    {
        return $this->hasMany(Yearlevelpayments::class, 'yearlevel_id1');
    }

    // public function getTotalFormattedAmountAttribute(): string
    // {
    //     $totalAmount = $this->yearlevelpayments->sum('amount');

    //     return '₱'.number_format($totalAmount, 2);
    // }

    public function getFormattedAmountAttribute(): array
    {
        // Get formatted individual amounts with descriptions
        return $this->yearlevelpayments->map(function ($payment) {
            return '₱' . number_format($payment->amount, 2) . ' - ' . $payment->description;
        })->toArray();
    }

    public function scolleges(): HasMany
    {
        return $this->hasMany(Scollege::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'yearlevel_id');
    }

    public function enrollmentForThisYearLevel()
    {
        return $this->hasOne(Enrollment::class, 'yearlevel_id');
    }
}
