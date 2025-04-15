<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'stud_id',
        'college_id',
        'program_id',
        'yearlevel_id',
        'schoolyear_id',
        'status',
    ];

    public function stud(): BelongsTo
    {
        return $this->belongsTo(Stud::class);
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

    public function schoolyear(): BelongsTo
    {
        return $this->belongsTo(Schoolyear::class);
    }

    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'enrollment_semester')
            ->withTimestamps();
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_enrollment')
            ->withTimestamps()
            ->withPivot('collection_status');
    }

    public function yearlevelpayments(): BelongsToMany
    {
        return $this->belongsToMany(Yearlevelpayments::class)
            ->withTimestamps();
    }

    public function getFormattedCollectionsAttribute(): array
    {
        return $this->collections()->map(function ($collection) {
            return '₱' . number_format($collection->amount, 2) . ' - ' . $collection->description;
        })->toArray();
    }

    public function getFormattedYearlevelPaymentsAttribute(): array
    {
        return $this->yearlevelpayments()->map(function ($payment) {
            return '₱' . number_format($payment->amount, 2) . ' - ' . $payment->description;
        })->toArray();
    }

    public function getTotalPaymentsAttribute(): string
    {
        $collectionsTotal = $this->collections()->sum('amount');
        $yearlevelPaymentsTotal = $this->yearlevelpayments()->sum('amount');

        $total = $collectionsTotal + $yearlevelPaymentsTotal;

        return '₱' . number_format($total, 2, '.', ',');
    }

    public function totalPaymentsAttribute(): string
    {
        $collectionsTotal = $this->collections()->sum('amount');
        $yearlevelPaymentsTotal = $this->yearlevelpayments()->sum('amount');

        $total = $collectionsTotal + $yearlevelPaymentsTotal;

        return '₱' . number_format($total, 2, '.', ',');
    }

    public function getAmountWithDescriptionAttribute(): string
    {
        return '₱' . number_format($this->amount, 2, '.', ',') . ' - ' . $this->description;
    }

    public function pays(): HasMany
    {
        return $this->hasMany(Pay::class);
    }

    public function getTotalPaysAmountAttribute(): string
    {
        $totalAmount = $this->pays()->sum('amount');

        return '₱' . number_format($totalAmount, 2);
    }

    public function getBalanceAttribute(): string
    {
        $collectionsTotal = $this->collections()->sum('amount');
        $yearlevelPaymentsTotal = $this->yearlevelpayments()->sum('amount');
        $amountPaid = $this->pays()->where('status1', 'paid')->sum('amount');
        $amountRefunded = $this->pays()->where('status1', 'refunded')->sum('amount');
        $totalAmount = $amountPaid - $amountRefunded;

        if ($collectionsTotal == 0 && $yearlevelPaymentsTotal == 0) {
            return 0;
        }

        $balance = ($collectionsTotal + $yearlevelPaymentsTotal) - $totalAmount;

        return number_format($balance, 2);
    }

    public function getRefundableAmountAttribute(): string
    {
        $collectionsTotal = $this->collections()->sum('amount');
        $yearlevelPaymentsTotal = $this->yearlevelpayments()->sum('amount');
        $amountPaid = $this->pays()->where('status1', 'paid')->sum('amount');
        $amountRefunded = $this->pays()->where('status1', 'refunded')->sum('amount');
        $totalCollected = $amountPaid - $amountRefunded;

        // Expected total is the sum of collections and yearlevel payments
        $expectedTotal = $collectionsTotal + $yearlevelPaymentsTotal;

        // Refundable amount is when total collected exceeds expected total
        // (opposite of a balance, which is when expected total exceeds collected)
        $refundableAmount = max(0, $totalCollected - $expectedTotal);

        if ($collectionsTotal == 0 && $yearlevelPaymentsTotal == 0 && $refundableAmount == 0) {
            return 'No Refund';
        }

        return number_format($refundableAmount, 2);
    }

    public function getTotalCollectiontAttribute(): string
    {
        $collectionsTotal = $this->collections()->sum('amount');
        $yearlevelPaymentsTotal = $this->yearlevelpayments()->sum('amount');

        // Expected total is the sum of collections and yearlevel payments
        $expectedTotal = $collectionsTotal + $yearlevelPaymentsTotal;

        // Refundable amount is when total collected exceeds expected total
        // (opposite of a balance, which is when expected total exceeds collected)

        if ($expectedTotal == 0) {
            return 'No collections';
        }

        return number_format($expectedTotal, 2);
    }

    public function getPaymentsAttribute(): string
    {
        $amountPaid = $this->pays()->where('status1', 'paid')->sum('amount');
        $amountRefunded = $this->pays()->where('status1', 'refunded')->sum('amount');
        $collectionsTotal = $amountPaid - $amountRefunded;

        if ($collectionsTotal == 0) {
            return 'No Payments';
        }

        return number_format($collectionsTotal, 2);
    }

    public static function summarizeAmounts(?int $schoolYearId): string
    {
        $total = self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->with(['collections', 'yearlevelpayments'])  // Eager load relationships
            ->get()
            ->fresh() // Ensures fresh data from the database
            ->sum(function ($enrollment) {
                // Initialize totals
                $collectionsTotal = $enrollment->collections->sum('amount');
                $yearLevelPaymentsTotal = $enrollment->yearlevelpayments->sum('amount');

                return $collectionsTotal + $yearLevelPaymentsTotal;
            });

        // Format the result as currency
        return number_format($total, 2, '.', ',');
    }

    public static function summarizePaysAmount(?int $schoolYearId): string
    {
        $total = self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->with(['pays'])  // Eager load the pays relationship
            ->get()
            ->fresh() // Ensures fresh data from the database
            ->sum(function ($enrollment) {
                // Sum the amounts in the `pays` relationship for each enrollment
                return $enrollment->pays->sum('amount');
            });

        return '₱' . number_format($total, 2, '.', ',');
    }

    public static function summarizeBalance(?int $schoolYearId): string
    {
        $totalBalance = self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->with(['collections', 'yearlevelpayments', 'pays'])  // Eager load all related data
            ->get()
            ->fresh() // Ensures fresh data from the database
            ->sum(function ($enrollment) {
                // Calculate the total for collections and yearlevelpayments
                $collectionsTotal = $enrollment->collections->sum('amount');
                $yearLevelPaymentsTotal = $enrollment->yearlevelpayments->sum('amount');
                // Calculate the total of pays
                $totalPays = $enrollment->pays->sum('amount');

                // Calculate the balance: total collections + yearlevel payments - total pays
                $balance = ($collectionsTotal + $yearLevelPaymentsTotal) - $totalPays;

                return $balance;
            });

        return '₱' . number_format($totalBalance, 2, '.', ',');
    }

    public static function countBySchoolYear(?int $schoolYearId): int
    {
        if (! $schoolYearId) {
            // Return the total count of students if no schoolyear_id is provided
            return self::count();
        }

        return self::where('schoolyear_id', $schoolYearId)->count();
    }

    public static function countFullyPaidStudents(?int $schoolYearId): int
    {
        // Perform the sum operation directly in the query to avoid loading all enrollments
        return self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->select('enrollments.id') // Select only necessary columns
            ->with(['collections', 'yearlevelpayments', 'pays']) // Eager load related models
            ->get()
            ->filter(function ($enrollment) {
                $collectionsTotal = $enrollment->collections->sum('amount');
                $yearLevelPaymentsTotal = $enrollment->yearlevelpayments->sum('amount');
                $totalPays = $enrollment->pays->sum('amount');

                // Fully paid if total pays >= total required amount
                return $totalPays >= ($collectionsTotal + $yearLevelPaymentsTotal);
            })
            ->count();
    }

    public static function countUnpaidStudents(?int $schoolYearId): int
    {
        // Perform the sum operation directly in the query to avoid loading all enrollments
        return self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->select('enrollments.id') // Select only necessary columns
            ->with(['collections', 'yearlevelpayments', 'pays']) // Eager load related models
            ->get()
            ->filter(function ($enrollment) {
                $collectionsTotal = $enrollment->collections->sum('amount');
                $yearLevelPaymentsTotal = $enrollment->yearlevelpayments->sum('amount');
                $totalPays = $enrollment->pays->sum('amount');

                // Unpaid if total pays < total required amount
                return $totalPays < ($collectionsTotal + $yearLevelPaymentsTotal);
            })
            ->count();
    }

    public static function countStudentsPerProgram(?int $schoolYearId): array
    {
        // Optimize by using only necessary fields and eager loading
        $programCounts = self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->select('program_id') // Only select necessary column
            ->withCount('program') // Use withCount to count programs directly
            ->groupBy('program_id') // Group by program
            ->get()
            ->mapWithKeys(function ($enrollment) {
                return [$enrollment->program->name => $enrollment->program_count]; // Map to program name and count
            });

        return $programCounts->toArray();
    }

    public static function countStudentsPerCollege(?int $schoolYearId): array
    {
        // Optimize by using only necessary fields and eager loading
        $collegeCounts = self::when($schoolYearId, function ($query) use ($schoolYearId) {
            return $query->where('schoolyear_id', $schoolYearId);
        })
            ->select('college_id') // Only select necessary column
            ->withCount('college') // Use withCount to count colleges directly
            ->groupBy('college_id') // Group by college
            ->get()
            ->mapWithKeys(function ($enrollment) {
                return [$enrollment->college->name => $enrollment->college_count]; // Map to college name and count
            });

        return $collegeCounts->toArray();
    }

    // public static function countBySchoolYear(?int $schoolYearId): int
    // {
    //     if (! $schoolYearId) {
    //         // Return the total count of students if no schoolyear_id is provided
    //         return self::count();
    //     }

    //     return self::when($schoolYearId, function ($query) use ($schoolYearId) {
    //         return $query->where('schoolyear_id', $schoolYearId);
    //     })->count();
    // }

    // public static function countFullyPaidStudents(?int $schoolYearId): int
    // {
    //     return self::when($schoolYearId, function ($query) use ($schoolYearId) {
    //         return $query->where('schoolyear_id', $schoolYearId);
    //     })
    //         ->get()
    //         ->filter(function ($enrollment) {
    //             $collectionsTotal = $enrollment->collections->sum('amount');
    //             $yearLevelPaymentsTotal = $enrollment->yearlevelpayments->sum('amount');
    //             $totalPays = $enrollment->pays->sum('amount');

    //             // Fully paid if total pays >= total required amount
    //             return $totalPays >= ($collectionsTotal + $yearLevelPaymentsTotal);
    //         })
    //         ->count();
    // }

    // public static function countUnpaidStudents(?int $schoolYearId): int
    // {
    //     return self::when($schoolYearId, function ($query) use ($schoolYearId) {
    //         return $query->where('schoolyear_id', $schoolYearId);
    //     })
    //         ->get()
    //         ->filter(function ($enrollment) {
    //             $collectionsTotal = $enrollment->collections->sum('amount');
    //             $yearLevelPaymentsTotal = $enrollment->yearlevelpayments->sum('amount');
    //             $totalPays = $enrollment->pays->sum('amount');

    //             // Unpaid if total pays < total required amount
    //             return $totalPays < ($collectionsTotal + $yearLevelPaymentsTotal);
    //         })
    //         ->count();
    // }

    // public static function countStudentsPerProgram(?int $schoolYearId): array
    // {
    //     $enrollments = self::when($schoolYearId, function ($query) use ($schoolYearId) {
    //         return $query->where('schoolyear_id', $schoolYearId);
    //     })
    //         ->with(['program']) // Eager load the program relationship
    //         ->get();

    //     // Group enrollments by program and count the students
    //     $programCounts = $enrollments->groupBy(function ($enrollment) {
    //         return $enrollment->program->name; // Group by program name
    //     })->map(function ($group) {
    //         return $group->count(); // Count students in each program
    //     });

    //     // Convert to array format
    //     return $programCounts->toArray();
    // }

    // public static function countStudentsPerCollege(?int $schoolYearId): array
    // {
    //     $enrollments = self::when($schoolYearId, function ($query) use ($schoolYearId) {
    //         return $query->where('schoolyear_id', $schoolYearId);
    //     })
    //         ->with(['college']) // Eager load the program relationship
    //         ->get();

    //     // Group enrollments by program and count the students
    //     $programCounts = $enrollments->groupBy(function ($enrollment) {
    //         return $enrollment->college->name; // Group by program name
    //     })->map(function ($group) {
    //         return $group->count(); // Count students in each program
    //     });

    //     // Convert to array format
    //     return $programCounts->toArray();
    // }
}
