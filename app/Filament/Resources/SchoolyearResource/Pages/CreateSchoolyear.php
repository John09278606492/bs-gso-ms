<?php

namespace App\Filament\Resources\SchoolyearResource\Pages;

use App\Filament\Resources\SchoolyearResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateSchoolyear extends CreateRecord
{
    protected static string $resource = SchoolyearResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Create School Year');
    }
}
