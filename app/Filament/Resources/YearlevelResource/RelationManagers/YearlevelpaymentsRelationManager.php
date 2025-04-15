<?php

namespace App\Filament\Resources\YearlevelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use stdClass;

class YearlevelpaymentsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;

    protected static string $relationship = 'yearlevelpayments';

    protected static ?string $title = 'Fee Types ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('yearlevel_id')
                            ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->id)
                            ->hidden(),
                        Forms\Components\TextInput::make('amount')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/[^\d.]/g, "").replace(/(\..*?)\.+/g, "$1").replace(/\B(?=(\d{3})+(?!\d))/g, ",")'])
                            ->numeric()
                            ->prefixIcon('heroicon-m-peso-symbol')
                            ->required(),
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->minLength(2)
                            ->maxLength(60)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())']),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('#')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP'),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New fee type')
                    ->createAnother(false)
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Fee Type added successfully!')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Fee Type updated successfully!')),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Fee Type deleted successfully!')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->color('success')
                                ->icon('heroicon-o-check-circle')
                                ->title('Fee Types deleted successfully!')),
                ]),
            ])
            ->emptyStateHeading('No fee types yet')
            ->emptyStateDescription('Once you add fee type, it will appear here.');
    }
}
