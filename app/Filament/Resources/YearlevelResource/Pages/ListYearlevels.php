<?php

namespace App\Filament\Resources\YearlevelResource\Pages;

use App\Filament\Resources\YearlevelResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListYearlevels extends ListRecords
{
    protected static string $resource = YearlevelResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New year level')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->title('Year Level added successfully!')
                ),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('Year Levels');
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
