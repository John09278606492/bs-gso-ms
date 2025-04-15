<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Enrollment;
use App\Models\Yearlevelpayments;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class BigStats extends BaseWidget
{
    use InteractsWithPageFilters;
    private function calculateExpectedCollections(): string
    {
        $schoolyearId = $this->filters['schoolyear_id'] ?? null;

        if ($schoolyearId === '' || $schoolyearId === 'All') {
            $schoolyearId = null;
        }

        // Query for collections total (excluding yearlevelpayments)
        $collectionsTotal = Enrollment::query()
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId);
            })
            ->leftJoin('collection_enrollment', 'enrollments.id', '=', 'collection_enrollment.enrollment_id')
            ->leftJoin('collections', 'collection_enrollment.collection_id', '=', 'collections.id')
            ->selectRaw('COALESCE(SUM(collections.amount), 0) as total')
            ->value('total');

        // Query for yearlevelpayments separately to avoid duplicates
        $yearLevelTotal = YearLevelPayments::query()
            ->whereHas('enrollments', function ($query) use ($schoolyearId) {
                if ($schoolyearId) {
                    $query->where('schoolyear_id', $schoolyearId);
                }
            })
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        // Calculate total expected collections
        $totalAmount = $collectionsTotal + $yearLevelTotal;

        return '₱' . number_format($totalAmount ?? 0, 2, '.', ',');
    }

    private function caculateTotalPays(): string
    {
        $schoolyearId = $this->filters['schoolyear_id'] ?? null;

        if ($schoolyearId === '' || $schoolyearId === 'All') {
            $schoolyearId = null;
        }

        $totalAmount = Enrollment::query()
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId); // Filter by school year if provided
            })
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'paid')
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total') - Enrollment::query()
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId); // Filter by school year if provided
            })
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'refunded')
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        return '₱' . number_format($totalAmount ?? 0, 2, '.', ',');
    }

    private function calculateRemainingCollections(): string
    {
        $schoolyearId = $this->filters['schoolyear_id'] ?? null;

        if ($schoolyearId === '' || $schoolyearId === 'All') {
            $schoolyearId = null;
        }

        // Query for expected collections (excluding yearlevelpayments)
        $collectionsTotal = Enrollment::query()
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId);
            })
            ->leftJoin('collection_enrollment', 'enrollments.id', '=', 'collection_enrollment.enrollment_id')
            ->leftJoin('collections', 'collection_enrollment.collection_id', '=', 'collections.id')
            ->selectRaw('COALESCE(SUM(collections.amount), 0) as total')
            ->value('total');

        // Query for yearlevelpayments separately to avoid duplicate calculations
        $yearLevelTotal = Yearlevelpayments::query()
            ->whereHas('enrollments', function ($query) use ($schoolyearId) {
                if ($schoolyearId) {
                    $query->where('schoolyear_id', $schoolyearId);
                }
            })
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        // Query for collected payments
        $paidTotal = Enrollment::query()
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId);
            })
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'paid')
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total') - Enrollment::query()
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId); // Filter by school year if provided
            })
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'refunded')
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        // Calculate expected total
        $expectedTotal = $collectionsTotal + $yearLevelTotal;

        // Calculate remaining collections
        $remaining = max(0, $expectedTotal - $paidTotal);

        return '₱' . number_format($remaining, 2, '.', ',');
    }

    private function calculateRefundableAmount(): string
    {
        // Get selected schoolyear ID from filters
        $schoolyearId = $this->filters['schoolyear_id'] ?? null;

        if ($schoolyearId === '' || $schoolyearId === 'All') {
            $schoolyearId = null;
        }

        // Fetch relevant enrollments for filtering
        $enrollmentIds = DB::table('enrollments')
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                return $query->where('enrollments.schoolyear_id', $schoolyearId);
            })
            ->pluck('id');

        // If there are no enrollments, return ₱0.00
        if ($enrollmentIds->isEmpty()) {
            return '₱0.00';
        }

        // Query calculations
        $result = DB::table('enrollments')
            ->whereIn('enrollments.id', $enrollmentIds)
            ->selectRaw('
            enrollments.id,
            COALESCE((
                SELECT SUM(c.amount)
                FROM collections c
                JOIN collection_enrollment ce ON c.id = ce.collection_id
                WHERE ce.enrollment_id = enrollments.id
            ), 0) + COALESCE((
                SELECT SUM(y.amount)
                FROM yearlevelpayments y
                JOIN enrollment_yearlevelpayments ey ON y.id = ey.yearlevelpayments_id
                WHERE ey.enrollment_id = enrollments.id
            ), 0) as expected_total,
            COALESCE((
                SELECT SUM(p.amount)
                FROM pays p
                WHERE p.enrollment_id = enrollments.id AND p.status1 = "paid"
            ), 0) as paid_total,
            COALESCE((
                SELECT SUM(p.amount)
                FROM pays p
                WHERE p.enrollment_id = enrollments.id AND p.status1 = "refunded"
            ), 0) as refunded_total
        ')
            ->get();

        // Calculate the total refundable amount by processing each enrollment record
        $totalRefundableAmount = $result->sum(function ($row) {
            $totalCollected = $row->paid_total - $row->refunded_total;
            return max(0, $totalCollected - $row->expected_total);
        });

        return '₱' . number_format($totalRefundableAmount, 2, '.', ',');
    }


    protected function getStats(): array
    {
        return [
            Stat::make('', $this->calculateExpectedCollections())
                ->description('Total Expected Collection')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('warning'),
            Stat::make('', $this->caculateTotalPays())
                ->description('Total Amount Collected')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('success'),
            Stat::make('', $this->calculateRemainingCollections())
                ->description('Total Running Balance')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('danger'),
            Stat::make('', $this->calculateRefundableAmount())
                ->description('Total Refundable Amount')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('warning'),
        ];
    }
}
