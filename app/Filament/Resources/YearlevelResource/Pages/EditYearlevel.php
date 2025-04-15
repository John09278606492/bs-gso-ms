<?php

namespace App\Filament\Resources\YearlevelResource\Pages;

use App\Filament\Resources\YearlevelResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditYearlevel extends EditRecord
{
    protected static string $resource = YearlevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->title('Year Level deleted successfully!')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function getContentTabLabel(): ?string
    {
        return 'Year Level';
    }

    public function getTitle(): string|Htmlable
    {
        return __('Edit Year Level');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
