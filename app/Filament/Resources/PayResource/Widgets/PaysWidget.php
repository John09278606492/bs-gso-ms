<?php

namespace App\Filament\Resources\PayResource\Widgets;

use App\Filament\Resources\PayResource\Pages\ListPays;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaysWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListPays::class;
    }

    private function calculateTotalPaid(): string
    {
        // Get total paid
        $paidTotal = $this->getPageTableQuery()
            ->where('status1', 'paid')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        return '₱' . number_format($paidTotal, 2, '.', ',');
    }

    private function calculateTotalRefunded(): string
    {
        // Get total refunded
        $refundedTotal = $this->getPageTableQuery()
            ->where('status1', 'refunded')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        return '₱' . number_format($refundedTotal, 2, '.', ',');
    }

    private function calculateNetTotal(): string
    {
        // Get total paid
        $paidTotal = $this->getPageTableQuery()
            ->where('status1', 'paid')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        // Get total refunded
        $refundedTotal = $this->getPageTableQuery()
            ->where('status1', 'refunded')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        // Net total
        $netTotal = $paidTotal - $refundedTotal;

        return '₱' . number_format($netTotal, 2, '.', ',');
    }


    protected function getStats(): array
    {
        return [
            Stat::make('', $this->calculateTotalPaid())
                ->description('Total Amount Paid)')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('warning'),
            Stat::make('', $this->calculateTotalRefunded())
                ->description('Total Amount Refunded')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('danger'),
            Stat::make('', $this->calculateNetTotal())
                ->description('Net Amount (Paid - Refunded)')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('success'),
        ];
    }
}
