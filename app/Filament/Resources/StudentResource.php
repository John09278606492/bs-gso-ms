<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Collection as ModelsCollection;
use App\Models\College;
use App\Models\Program;
use App\Models\Schoolyear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Yearlevel;
use App\Models\Yearlevelpayments;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationLabel = 'Student Information';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Details')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('studentidn')
                                    ->label('Student IDN')
                                    ->numeric()
                                    ->minValue(0)
                                    ->minLength(6)
                                    ->maxLength(15)
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columnStart(1),
                        Forms\Components\TextInput::make('firstname')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())']),
                        Forms\Components\TextInput::make('middlename')
                            ->label('Middle Name')
                            ->maxLength(255)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())']),
                        Forms\Components\TextInput::make('lastname')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())']),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'graduated' => 'Graduated',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Section::make('Student Course')
                    ->relationship('scolleges')
                    ->schema([
                        Forms\Components\Select::make('college_id')
                            ->label('College')
                            ->options(College::all()->pluck('college', 'id'))
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('program_id', null);
                                $set('yearlevel_id', null);
                                $set('yearlevelpayment_id', []);
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('program_id')
                            ->label('Program')
                            ->options(fn (Get $get): Collection => Program::query()
                                ->where('college_id', $get('college_id'))
                                ->pluck('program', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('yearlevel_id', null);
                                $set('yearlevelpayment_id', []);
                            })
                            ->required(),
                        Forms\Components\Select::make('yearlevel_id')
                            ->label('Year Level')
                            ->options(fn (Get $get): Collection => Yearlevel::query()
                                ->where('program_id', $get('program_id'))
                                ->pluck('yearlevel', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set) => $set('yearlevelpayment_id', []))
                            ->required(),
                        Forms\Components\Grid::make()
                            ->schema([
                                CheckboxList::make('yearlevelpayment')
                                    ->label('Year Level Fee')
                                    ->inlineLabel()
                                    ->relationship('yearlevelpayments', 'amount') // Define the relationship and the display column
                                    ->options(fn (Get $get): array => Yearlevelpayments::query()
                                        ->where('yearlevel_id', $get('yearlevel_id'))
                                        ->get()
                                        ->mapWithKeys(fn ($payment) => [
                                            $payment->id => '₱'.number_format($payment->amount, 2), // Only amount here
                                        ])
                                        ->toArray())
                                    ->descriptions(fn (Get $get): array => Yearlevelpayments::query()
                                        ->where('yearlevel_id', $get('yearlevel_id'))
                                        ->get()
                                        ->mapWithKeys(fn ($payment) => [
                                            $payment->id => new HtmlString(
                                                $payment->description
                                                    ? e($payment->description)
                                                    : '<em>No description available.</em>' // Use italics for no description
                                            ),
                                        ])
                                        ->toArray())
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // Keep the current state as is without clearing other selections
                                        if (! is_array($state)) {
                                            $set('yearlevelpayments', []);
                                        }
                                    })
                                    ->columns(4)
                                    ->gridDirection('row'),
                            ])
                            ->columnStart(1),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Student School Year')
                    ->relationship('syears')
                    ->schema([
                        Forms\Components\Select::make('schoolyear_id')
                            ->label('School Year')
                            ->options(Schoolyear::all()->pluck('schoolyear', 'id'))
                            ->preload()
                            ->searchable()
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('semester_id', []);
                                $set('collection_id', []);
                            })
                            ->required(),
                        Forms\Components\CheckboxList::make('semester_id')
                            ->label('Semester')
                            ->inlineLabel()
                            ->relationship('semesters', 'semester')
                            ->options(fn (Get $get): array => Semester::query()
                                ->where('schoolyear_id', $get('schoolyear_id'))
                                ->pluck('semester', 'id')
                                ->toArray())
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('collection_id', []);
                            })
                            ->columns(2)
                            ->gridDirection('row'),
                        CheckboxList::make('collection_id')
                            ->label('Fee Type')
                            ->inlineLabel()
                            ->relationship('collections', 'amount') // Adjusted to match the relationship name and attribute in your model
                            ->options(fn (Get $get): array => ModelsCollection::query()
                                ->whereIn('semester_id', $get('semester_id'))
                                ->get()
                                ->mapWithKeys(fn ($collection) => [
                                    $collection->id => '₱'.number_format($collection->amount, 2), // Only amount here
                                ])
                                ->toArray())
                            ->descriptions(fn (Get $get): array => ModelsCollection::query()
                                ->whereIn('semester_id', $get('semester_id'))
                                ->with('semester') // Eager load the semester relationship
                                ->get()
                                ->mapWithKeys(fn ($collection) => [
                                    $collection->id => new HtmlString(
                                        ($collection->description
                                            ? e($collection->description)
                                            : '<em>No description available.</em>') // Payment description
                                        .'<br>'
                                        .'<small>Semester: '.e(optional($collection->semester)->semester ?? 'Unknown Semester').'</small>' // Add semester type
                                    ),
                                ])
                                ->toArray())
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                // Keep the current state as is without clearing other selections
                                if (! is_array($state)) {
                                    $set('collection_id', []);
                                }
                            })
                            ->columns(2)
                            ->gridDirection('row'),

                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('studentidn')
                    ->label('Student IDN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable([
                        'lastname', 'firstname', 'middlename',
                    ]),
                Tables\Columns\TextColumn::make('scolleges.college.college')
                    ->label('College'),
                Tables\Columns\TextColumn::make('scolleges.program.program')
                    ->label('Program'),
                Tables\Columns\TextColumn::make('scolleges.yearlevel.yearlevel')
                    ->label('Year Level'),
                Tables\Columns\TextColumn::make('syears.schoolyear.schoolyear')
                    ->label('School Year'),
                // Tables\Columns\TextColumn::make('combinedTotalAmount')
                //     ->label('School Year Total')
                //     ->badge()
                //     ->color(fn (string $state) => floatval(str_replace(['₱', ','], '', $state)) == 0.0
                //             ? 'success'
                //             : (floatval(str_replace(['₱', ','], '', $state)) < 0 ? 'warning' : 'danger')
                //     ),
                Tables\Columns\TextColumn::make('combinedTotalAmount')
                    ->label('Expected Collection Amount')
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->color('gray')
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Expected Collection Amount')
                            ->using(fn () => Student::all() // Load all student records as models
                                ->sum(fn ($student) => floatval(str_replace(['₱', ','], '', $student->combinedTotalAmount))
                                )
                            )
                            ->formatStateUsing(fn ($state) => '₱'.number_format($state, 2))
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
