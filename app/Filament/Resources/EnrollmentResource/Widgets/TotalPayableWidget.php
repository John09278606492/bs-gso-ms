<?php

namespace App\Filament\Resources\EnrollmentResource\Widgets;

use App\Filament\Resources\EnrollmentResource\Pages\ListEnrollments;
use App\Models\Enrollment;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TotalPayableWidget extends BaseWidget
{
    use InteractsWithPageTable;

    // protected static bool $isLazy = true;

    // protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListEnrollments::class;
    }

    private function calculateExpectedCollections(): string
    {
        // Get expected collections
        $collectionsTotal = $this->getPageTableQuery()
            ->leftJoin('collection_enrollment', 'enrollments.id', '=', 'collection_enrollment.enrollment_id')
            ->leftJoin('collections', 'collection_enrollment.collection_id', '=', 'collections.id')
            ->reorder()
            ->selectRaw('COALESCE(SUM(collections.amount), 0) as total')
            ->value('total');

        // Get expected yearlevelpayments
        $yearLevelTotal = $this->getPageTableQuery()
            ->leftJoin('enrollment_yearlevelpayments', 'enrollments.id', '=', 'enrollment_yearlevelpayments.enrollment_id')
            ->reorder()
            ->leftJoin('yearlevelpayments', 'enrollment_yearlevelpayments.yearlevelpayments_id', '=', 'yearlevelpayments.id')
            ->selectRaw('COALESCE(SUM(yearlevelpayments.amount), 0) as total')
            ->value('total');

        // Total expected amount
        $expectedTotal = $collectionsTotal + $yearLevelTotal;

        return '₱' . number_format($expectedTotal, 2, '.', ',');
    }

    private function calculateCollectedAmounts(): string
    {
        $paidTotal1 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'paid')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $paidTotal2 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'refunded')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $totalAmount = $paidTotal1 - $paidTotal2;

        return '₱' . number_format($totalAmount ?? 0, 2, '.', ',');
    }

    private function expectedCollectedAmounts(): string
    {
        $paidTotal1 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'paid')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $paidTotal2 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'refunded')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $totalAmount = $paidTotal1 - $paidTotal2;

        $result = DB::table('enrollments')
            ->whereIn('enrollments.id', $this->getPageTableQuery()->pluck('id'))
            ->reorder()
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

        $finalAmount = $totalAmount - $totalRefundableAmount;

        return '₱' . number_format($finalAmount ?? 0, 2, '.', ',');
    }

    // private function calculateRefundableAmount(): string
    // {
    //     $enrollments = $this->getPageTableQuery()
    //         ->with(['collections', 'yearlevelpayments', 'pays'])
    //         ->get();

    //     $totalRefundableAmount = $enrollments->sum(function ($enrollment) {
    //         $collectionsTotal = $enrollment->collections->sum('amount');
    //         $yearLevelTotal = $enrollment->yearlevelpayments->sum('amount');
    //         $expectedTotal = $collectionsTotal + $yearLevelTotal;

    //         $paidTotal = $enrollment->pays->where('status1', 'paid')->sum('amount');
    //         $refundedTotal = $enrollment->pays->where('status1', 'refunded')->sum('amount');

    //         // Collected after accounting for refunds
    //         $totalCollected = $paidTotal - $refundedTotal;

    //         // Refundable Amount Calculation (Allow Negative Values)
    //         return max(0, $totalCollected - $expectedTotal);
    //     });

    //     return '₱' . number_format($totalRefundableAmount, 2, '.', ',');
    // }

    private function calculateRefundableAmount(): string
    {
        // Create a query to calculate refundable amount for each enrollment in one go
        $result = DB::table('enrollments')
            ->whereIn('enrollments.id', $this->getPageTableQuery()->pluck('id'))
            ->reorder()
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

    private function calculateRemainingCollections(): string
    {
        // Get expected collections
        $collectionsTotal = $this->getPageTableQuery()
            ->leftJoin('collection_enrollment', 'enrollments.id', '=', 'collection_enrollment.enrollment_id')
            ->leftJoin('collections', 'collection_enrollment.collection_id', '=', 'collections.id')
            ->reorder()
            ->selectRaw('COALESCE(SUM(collections.amount), 0) as total')
            ->value('total');

        // Get expected yearlevelpayments
        $yearLevelTotal = $this->getPageTableQuery()
            ->leftJoin('enrollment_yearlevelpayments', 'enrollments.id', '=', 'enrollment_yearlevelpayments.enrollment_id')
            ->leftJoin('yearlevelpayments', 'enrollment_yearlevelpayments.yearlevelpayments_id', '=', 'yearlevelpayments.id')
            ->reorder()
            ->selectRaw('COALESCE(SUM(yearlevelpayments.amount), 0) as total')
            ->value('total');

        // Total expected amount
        $expectedTotal = $collectionsTotal + $yearLevelTotal;

        // Get collected payments
        $paidTotal1 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'paid')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $paidTotal2 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'refunded')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $paidTotal = $paidTotal1 - $paidTotal2;

        // Ensure no negative values
        $remaining = max(0, $expectedTotal - $paidTotal);

        return '₱' . number_format($remaining, 2, '.', ',');
    }

    private function expectedRemainingCollections(): string
    {
        // Get expected collections
        $collectionsTotal = $this->getPageTableQuery()
            ->leftJoin('collection_enrollment', 'enrollments.id', '=', 'collection_enrollment.enrollment_id')
            ->leftJoin('collections', 'collection_enrollment.collection_id', '=', 'collections.id')
            ->reorder()
            ->selectRaw('COALESCE(SUM(collections.amount), 0) as total')
            ->value('total');

        // Get expected yearlevelpayments
        $yearLevelTotal = $this->getPageTableQuery()
            ->leftJoin('enrollment_yearlevelpayments', 'enrollments.id', '=', 'enrollment_yearlevelpayments.enrollment_id')
            ->leftJoin('yearlevelpayments', 'enrollment_yearlevelpayments.yearlevelpayments_id', '=', 'yearlevelpayments.id')
            ->reorder()
            ->selectRaw('COALESCE(SUM(yearlevelpayments.amount), 0) as total')
            ->value('total');

        // Total expected amount
        $expectedTotal = $collectionsTotal + $yearLevelTotal;

        $paidTotal1 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'paid')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $paidTotal2 = $this->getPageTableQuery()
            ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
            ->where('pays.status1', 'refunded')
            ->reorder()
            ->selectRaw('COALESCE(SUM(pays.amount), 0) as total')
            ->value('total');

        $totalAmount = $paidTotal1 - $paidTotal2;

        $result = DB::table('enrollments')
            ->whereIn('enrollments.id', $this->getPageTableQuery()->pluck('id'))
            ->reorder()
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

        $finalAmount = $totalAmount - $totalRefundableAmount;

        // Ensure no negative values
        $remaining = max(0, $expectedTotal - $finalAmount);

        return '₱' . number_format($remaining, 2, '.', ',');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('', $this->calculateExpectedCollections())
                ->description('Total Expected Collection')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('primary'),
            Stat::make('', $this->calculateCollectedAmounts())
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
            // Stat::make('', $this->expectedCollectedAmounts())
            //     ->description('Expected Total Amount Collected')
            //     ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
            //     ->color('success'),
            // Stat::make('', $this->expectedRemainingCollections())
            //     ->description('Expected Total Outstanding Balance')
            //     ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
            //     ->color('danger'),
            // Stat::make('Total', Enrollment::summarizeAmounts())
            //     ->description('Amount Paid')
            //     ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
            //     ->color('success'),
        ];
    }
}
