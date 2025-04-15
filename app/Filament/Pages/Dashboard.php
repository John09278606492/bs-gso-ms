<?php

namespace App\Filament\Pages;

use App\Models\Collection;
use App\Models\Schoolyear;
use App\Models\Semester;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected ?string $maxContentWidth = 'full';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function filtersForm(Form $form): Form
    {
        $today = Carbon::today();
        $defaultSchoolYearId = Schoolyear::where('startDate', '<=', $today)
            ->where('endDate', '>=', $today)
            ->value('id');

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('schoolyear_id')
                            ->inlineLabel(false)
                            ->label('School Year')
                            ->prefixIcon('heroicon-m-funnel')
                            ->selectablePlaceholder(false)
                            ->default($defaultSchoolYearId ?? 'All')
                            ->options(['All' => 'All'] + Schoolyear::all()->pluck('schoolyear', 'id')->toArray())
                            ->nullable()
                            ->columnSpanFull()
                            ->hint('Select a School Year'),
                        // Select::make('semester_id')
                        //     ->label('Semester')
                        //     ->multiple()
                        //     ->options(fn(Get $get) => Semester::query()
                        //         ->where('schoolyear_id', $get('schoolyear_id'))
                        //         ->pluck('semester', 'id'))
                        //     ->afterStateUpdated(function (Set $set, $state, $livewire) {
                        //         $set('collection_id', null);
                        //         // $livewire->dispatch('refresh');
                        //     })
                        //     ->preload()
                        //     ->reactive()
                        //     ->searchable(),
                        // Select::make('collection_id')
                        //     ->label('Collection')
                        //     ->multiple()
                        //     ->options(
                        //         fn(Get $get) => Collection::query()
                        //             ->whereIn('semester_id', (array) $get('semester_id'))
                        //             ->get() // Fetch full objects
                        //             ->mapWithKeys(fn($collection) => [
                        //                 $collection->id => $collection->description .
                        //                     ' - ₱' . number_format($collection->amount, 2) .
                        //                     ' (' . 'Semester: ' . optional($collection->semester)->semester . ')'
                        //             ])
                        //     )
                        //     ->preload()
                        //     ->searchable()
                        //     ->reactive(),
                    ])
                // ->columns(3)
            ]);
    }
}
