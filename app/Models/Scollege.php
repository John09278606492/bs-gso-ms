<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Scollege extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'college_id',
        'program_id',
        'yearlevel_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function yearlevel(): BelongsTo
    {
        return $this->belongsTo(Yearlevel::class);
    }

    public function yearlevelpayments(): BelongsToMany
    {
        return $this->belongsToMany(Yearlevelpayments::class, 'scollege_yearlevelpayment', 'scollege_id', 'yearlevelpayment_id')
            ->withTimestamps(); // Optional, if your pivot table has timestamps
    }

    public function getTotalAmountAttribute()
    {
        if ($this->yearlevelpayments->isEmpty()) {
            return '₱0.00'; // Return a default value if no yearlevelpayments are associated
        }

        $totalAmount = $this->yearlevelpayments->sum('amount'); // Sum of all 'amount' in related yearlevelpayments

        return '₱'.number_format($totalAmount, 2);
    }
}
