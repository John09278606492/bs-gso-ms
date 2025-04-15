<?php

namespace App\Filament\Resources\PayResource\Pages;

use App\Filament\Resources\PayResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePay extends CreateRecord
{
    protected static string $resource = PayResource::class;
}
