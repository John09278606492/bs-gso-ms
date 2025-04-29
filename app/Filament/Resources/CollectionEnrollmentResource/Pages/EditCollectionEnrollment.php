<?php

namespace App\Filament\Resources\CollectionEnrollmentResource\Pages;

use App\Filament\Resources\CollectionEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionEnrollment extends EditRecord
{
    protected static string $resource = CollectionEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
