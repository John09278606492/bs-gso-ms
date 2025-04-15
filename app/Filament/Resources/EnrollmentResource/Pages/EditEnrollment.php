<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Filament\Resources\EnrollmentResource\Widgets\TotalPayableWidget;
use App\Models\Collection;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Js;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    // protected static bool $saveChanges = true;

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s'])
            ->before(function (Action $action, array $data) {
                // ✅ Get the currently selected collection IDs from the form state
                $collectionIds = $data['collection_id'] ?? [];

                // ✅ Get total amount from selected collections
                $collectionTotal = Collection::whereIn('id', $collectionIds)->sum('amount');

                // ✅ Halt the save if total amount is zero
                if ($collectionTotal <= 0) {
                    Notification::make()
                        ->title('Action Denied')
                        ->body('A student cannot have zero fees. Please select the correct payments.')
                        ->danger()
                        ->send();

                    // ✅ Halt the save operation gracefully
                    $action->halt();
                }
            });
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('return')
                ->color('primary')
                ->icon('heroicon-m-arrow-left-circle')
                ->label('Go back')
                ->livewireClickHandlerEnabled()
                ->url($this->previousUrl ?? $this->getResource()::getUrl('index')),
            // Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function beforeSave(): void
    {
        // ✅ Get the currently selected collection and yearlevelpayment IDs from the form state
        $collectionIds = $this->data['collection_id'] ?? [];

        if (empty($collectionIds) || !is_array($collectionIds)) {
            Notification::make()
                ->title('Action Denied')
                ->body('No fees selected. Please select at least one collection.')
                ->danger()
                ->send();

            // ✅ Halt the action properly
            $this->halt();
            return;
        }
    }


    public function getContentTabLabel(): ?string
    {
        return 'Enrollment Info';
    }

    public function getTitle(): string|Htmlable
    {
        return __('Edit Student Payment');
    }

    // protected function afterSave(): void
    // {
    //     $enrollment = $this->record; // This is already the Enrollment model

    //     logger()->info("Processing Enrollment ID: {$enrollment->id}");

    //     // Get the latest balance
    //     $balance = $enrollment->getBalanceAttribute();
    //     $numericBalance = str_replace([',', '₱'], '', $balance);

    //     logger()->info("Enrollment ID: {$enrollment->id}, New Balance: {$numericBalance}");

    //     if ((float) $numericBalance <= 0) {
    //         $enrollment->update(['status' => 'paid']);
    //         logger()->info("Enrollment ID {$enrollment->id} marked as 'paid'.");
    //     } else {
    //         $enrollment->update(['status' => null]);
    //         logger()->info("Enrollment ID {$enrollment->id} status reset to NULL.");
    //     }
    // }

    protected function afterSave(): void
    {
        $enrollment = $this->record; // This is already the Enrollment model

        logger()->info("Processing Enrollment ID: {$enrollment->id}");

        // Get the latest balance
        $balance = $enrollment->getBalanceAttribute();
        $numericBalance = str_replace([',', '₱'], '', $balance);

        logger()->info("Enrollment ID: {$enrollment->id}, New Balance: {$numericBalance}");

        if ((float) $numericBalance <= 0) {
            // Mark the enrollment as paid
            $enrollment->update(['status' => 'paid']);
            logger()->info("Enrollment ID {$enrollment->id} marked as 'paid'.");

            // Update pivot table to mark selected collections as paid
            $selectedCollectionIds = $enrollment->collections->pluck('id')->toArray();

            if (!empty($selectedCollectionIds)) {
                $enrollment->collections()->updateExistingPivot($selectedCollectionIds, [
                    'collection_status' => 'paid',
                ]);
                logger()->info("Updated collection_status to 'paid' for collections: " . implode(', ', $selectedCollectionIds));
            }
        } else {
            $enrollment->update(['status' => null]);
            logger()->info("Enrollment ID {$enrollment->id} status reset to NULL.");
        }
    }


    // protected function getSaveFormAction(): Action
    // {
    //     return Action::make('save')
    //         ->label(__('Save enrollment'))
    //         ->submit('save')
    //         ->keyBindings(['mod+s'])
    //         ->hidden();
    // }

    // protected function getCancelFormAction(): Action
    // {
    //     return Action::make('cancel')
    //         ->label(__('Return'))
    //         ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = '.Js::from($this->previousUrl ?? static::getResource()::getUrl()).')')
    //         ->color('primary');
    // }

    // public function getHeaderWidgets(): array
    // {
    //     return [
    //         TotalPayableWidget::class,
    //     ];
    // }
}
