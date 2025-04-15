<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Yearlevelpayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'yearlevel_id1',
        'amount',
        'description',
    ];

    /**
     * Get the user that owns the Yearlevelpayments
     */
    public function yearlevel(): BelongsTo
    {
        return $this->belongsTo(Yearlevel::class, 'yearlevel_id1');
    }

    public function scolleges(): BelongsToMany
    {
        return $this->belongsToMany(Scollege::class, 'yearlevelpayment_scollege', 'yearlevelpayment_id', 'scollege_id')
            ->withTimestamps(); // Optional, if your pivot table has timestamps
    }

    public function enrollments(): BelongsToMany
    {
        return $this->belongsToMany(Enrollment::class, 'enrollment_yearlevelpayments')
            ->withTimestamps();
    }
}
