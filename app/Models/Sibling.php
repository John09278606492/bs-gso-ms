<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sibling extends Model
{
    use HasFactory;

    protected $fillable = [
        'stud_id',
        'sibling_id',
    ];

    public function stud(): BelongsTo
    {
        return $this->belongsTo(Stud::class, 'sibling_id');
    }
}
