<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'studentidn',
        'firstname',
        'middlename',
        'lastname',
        'status',
    ];

    public function scolleges(): HasOne
    {
        return $this->hasOne(Scollege::class);
    }

    public function syears(): HasOne
    {
        return $this->hasOne(Syear::class);
    }

    public function getFullNameAttribute()
    {
        return $this->lastname.', '.$this->firstname.', '.$this->middlename;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getCombinedTotalAmountAttribute()
    {
        $scollegeTotal = $this->scolleges ? $this->scolleges->getTotalAmountAttribute() : '₱0.00';
        $syearTotal = $this->syears ? $this->syears->getTotalAmountAttribute() : '₱0.00';

        // Remove the '₱' symbol and convert the amount to float for addition
        $scollegeTotal = floatval(str_replace(['₱', ','], '', $scollegeTotal));
        $syearTotal = floatval(str_replace(['₱', ','], '', $syearTotal));

        // Combine both totals
        $combinedTotal = $scollegeTotal + $syearTotal;

        // Return the combined total formatted with the '₱' symbol
        return '₱'.number_format($combinedTotal, 2);
    }

    public function getTotalCollectionAttribute()
    {
        return $this->scolleges->flatMap->yearlevelpayments->sum('amount');
    }
}
