<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pay extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'amount',
        'status1',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function generateReceipt()
    {
        return [
            'amount' => $this->amount,
        ];
    }

    public function invoiceRecords(): HasMany
    {
        return $this->hasMany(InvoiceRecord::class);
    }
}
