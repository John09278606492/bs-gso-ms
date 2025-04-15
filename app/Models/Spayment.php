<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'scollege_id',
        'yearlevelpayment_id',
    ];

    public function scollege(): BelongsTo
    {
        return $this->belongsTo(Scollege::class);
    }

    public function yearlevelpayment(): BelongsTo
    {
        return $this->belongsTo(Yearlevelpayments::class);
    }
}
