<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('student_id')
                //     ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->id)
                //     ->hidden(),
                Forms\Components\TextInput::make('amount')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/[^\d.]/g, "").replace(/(\..*?)\.+/g, "$1").replace(/\B(?=(\d{3})+(?!\d))/g, ",")'])
                    ->numeric()
                    // ->prefixIcon('heroicon-m-peso-symbol')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->summarize(Sum::make()
                        ->money()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time Paid')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New payment')
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Once you add payment, it will appear here.');
    }
}
