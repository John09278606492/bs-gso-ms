<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayResource\Pages;
use App\Filament\Resources\PayResource\RelationManagers;
use App\Models\Pay;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use stdClass;

class PayResource extends Resource
{
    protected static ?string $model = Pay::class;

    protected static ?string $breadcrumb = 'Payment Records';

    protected static ?string $navigationLabel = 'Payment Records';

    protected static ?string $navigationIcon = 'heroicon-m-banknotes';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('enrollment_id')
                    ->relationship('enrollment', 'id')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('status')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $dateToday = Carbon::today();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('enrollment.stud.studentidn')
                    ->label('Student IDN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.stud.full_name')
                    ->label('Complete Name')
                    ->weight(FontWeight::Bold)
                    ->sortable(['lastname', 'firstname', 'middlename'])
                    ->searchable([
                        'lastname',
                        'firstname',
                        'middlename',
                        'studentidn'
                    ]),
                Tables\Columns\TextColumn::make('enrollment.college.college')
                    ->label('College')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.program.program')
                    ->label('Program')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.yearlevel.yearlevel')
                    ->label('Year Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.schoolyear.schoolyear')
                    ->label('School Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status1')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date/Time Paid')
                    ->dateTime('M d, Y - h:i a')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable()
            ])
            ->pluralModelLabel('Pages')
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label('Date Range')
                    ->defaultToday(),
                // Filter::make('date_range')
                //     ->form([
                //         Forms\Components\DatePicker::make('start_date')
                //             ->label('Start Date')
                //             ->placeholder('Select start date')
                //             ->reactive()
                //             ->default($dateToday ?? null)
                //             ->afterStateUpdated(function ($state, callable $set) {
                //                 // Clear the end_date when start_date is changed or cleared
                //                 $set('end_date', null);
                //             }),
                //         Forms\Components\DatePicker::make('end_date')
                //             ->label('End Date')
                //             ->placeholder('Select end date')
                //             ->reactive()
                //             ->default($dateToday ?? null)
                //             ->minDate(fn (callable $get) => $get('start_date')), // Ensure end_date can't be before start_date
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['start_date'] ?? null,
                //                 fn (Builder $query, $startDate) => $query->whereDate('created_at', '>=', $startDate)
                //             )
                //             ->when(
                //                 $data['end_date'] ?? null,
                //                 fn (Builder $query, $endDate) => $query->whereDate('created_at', '<=', $endDate)
                //             );
                //     })
                //     ->indicateUsing(function (array $data): array {
                //         $indicators = [];

                //         if (! empty($data['start_date'])) {
                //             $indicators['start_date'] = 'Start Date: '.\Carbon\Carbon::parse($data['start_date'])->format('M d, Y');
                //         }

                //         if (! empty($data['end_date'])) {
                //             $indicators['end_date'] = 'End Date: '.\Carbon\Carbon::parse($data['end_date'])->format('M d, Y');
                //         }

                //         return $indicators;
                //     })
                //     ->columns(2)
                //     ->columnSpan(2),
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(2)
            ->deferLoading()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(),
            ])
            ->recordUrl(false)
            ->emptyStateHeading('No payment records found.');
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPays::route('/'),
            'create' => Pages\CreatePay::route('/create'),
            'edit' => Pages\EditPay::route('/{record}/edit'),
        ];
    }
}
