<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudResource\Pages;
use App\Filament\Resources\StudResource\RelationManagers;
use App\Filament\Resources\StudResource\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Resources\StudResource\RelationManagers\SiblingRelationManager;
use App\Models\Collection as ModelsCollection;
use App\Models\College;
use App\Models\Program;
use App\Models\Schoolyear;
use App\Models\Semester;
use App\Models\Stud;
use App\Models\User;
use App\Models\Yearlevel;
use App\Models\Yearlevelpayments;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use stdClass;

class StudResource extends Resource
{
    protected static ?string $model = Stud::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $breadcrumb = 'Student Information';

    protected static ?string $navigationLabel = 'Student Information';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Personal Information')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('studentidn')
                                    ->label('Student IDN')
                                    ->unique(ignoreRecord: true)
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
                            ]),
                        // ->required(),
                    ]),
                // Forms\Components\Section::make('Student Academic Information')
                //     ->relationship('enrollments')
                //     ->schema([
                //         Forms\Components\Select::make('college_id')
                //             ->label('College')
                //             ->options(College::all()->pluck('college', 'id'))
                //             ->preload()
                //             ->live()
                //             ->afterStateUpdated(function (Set $set) {
                //                 $set('program_id', null);
                //                 $set('yearlevel_id', null);
                //                 $set('yearlevelpayment_id', []);
                //             })
                //             ->searchable()
                //             ->required(),
                //         Forms\Components\Select::make('program_id')
                //             ->label('Program')
                //             ->options(fn (Get $get): Collection => Program::query()
                //                 ->where('college_id', $get('college_id'))
                //                 ->pluck('program', 'id'))
                //             ->searchable()
                //             ->preload()
                //             ->live()
                //             ->afterStateUpdated(function (Set $set) {
                //                 $set('yearlevel_id', null);
                //                 $set('yearlevelpayment_id', []);
                //             })
                //             ->required(),
                //         Forms\Components\Select::make('yearlevel_id')
                //             ->label('Year Level')
                //             ->options(fn (Get $get): Collection => Yearlevel::query()
                //                 ->where('program_id', $get('program_id'))
                //                 ->pluck('yearlevel', 'id'))
                //             ->searchable()
                //             ->preload()
                //             ->live()
                //             ->reactive()
                //             ->afterStateUpdated(fn (Set $set) => $set('yearlevelpayment_id', []))
                //             ->required(),
                //         CheckboxList::make('yearlevelpayment_id')
                //             ->label('Year Level Fee')
                //             ->inlineLabel()
                //             ->relationship('yearlevelpayments', 'amount') // Define the relationship and the display column
                //             ->options(fn (Get $get): array => Yearlevelpayments::query()
                //                 ->where('yearlevel_id', $get('yearlevel_id'))
                //                 ->get()
                //                 ->mapWithKeys(fn ($payment) => [
                //                     $payment->id => '₱'.number_format($payment->amount, 2), // Only amount here
                //                 ])
                //                 ->toArray())
                //             ->descriptions(fn (Get $get): array => Yearlevelpayments::query()
                //                 ->where('yearlevel_id', $get('yearlevel_id'))
                //                 ->get()
                //                 ->mapWithKeys(fn ($payment) => [
                //                     $payment->id => new HtmlString(
                //                         $payment->description
                //                             ? e($payment->description)
                //                             : '<em>No description available.</em>' // Use italics for no description
                //                     ),
                //                 ])
                //                 ->toArray())
                //             ->live()
                //             ->afterStateUpdated(function ($state, Set $set) {
                //                 // Keep the current state as is without clearing other selections
                //                 if (! is_array($state)) {
                //                     $set('yearlevelpayments', []);
                //                 }
                //             })
                //             ->columns(4)
                //             ->gridDirection('row'),
                //         Forms\Components\Grid::make()
                //             ->schema([
                //                 Forms\Components\Select::make('schoolyear_id')
                //                     ->label('School Year')
                //                     ->options(Schoolyear::all()->pluck('schoolyear', 'id'))
                //                     ->preload()
                //                     ->searchable()
                //                     ->live()
                //                     ->reactive()
                //                     ->afterStateUpdated(function ($state, Set $set) {
                //                         $set('semester_id', []);
                //                         $set('collection_id', []);
                //                     })
                //                     ->required(),
                //             ])
                //             ->columnStart(1),
                //         Forms\Components\CheckboxList::make('semester_id')
                //             ->label('Semester')
                //             ->inlineLabel()
                //             ->relationship('semesters', 'semester')
                //             ->options(fn (Get $get): array => Semester::query()
                //                 ->where('schoolyear_id', $get('schoolyear_id'))
                //                 ->pluck('semester', 'id')
                //                 ->toArray())
                //             ->live()
                //             ->reactive()
                //             ->afterStateUpdated(function ($state, Set $set) {
                //                 $set('collection_id', []);
                //             })
                //             ->columns(2)
                //             ->gridDirection('row'),
                //         CheckboxList::make('collection_id')
                //             ->label('Semester Fee Type')
                //             ->inlineLabel()
                //             ->relationship('collections', 'amount') // Adjusted to match the relationship name and attribute in your model
                //             ->options(fn (Get $get): array => ModelsCollection::query()
                //                 ->whereIn('semester_id', $get('semester_id'))
                //                 ->get()
                //                 ->mapWithKeys(fn ($collection) => [
                //                     $collection->id => '₱'.number_format($collection->amount, 2), // Only amount here
                //                 ])
                //                 ->toArray())
                //             ->descriptions(fn (Get $get): array => ModelsCollection::query()
                //                 ->whereIn('semester_id', $get('semester_id'))
                //                 ->with('semester') // Eager load the semester relationship
                //                 ->get()
                //                 ->mapWithKeys(fn ($collection) => [
                //                     $collection->id => new HtmlString(
                //                         ($collection->description
                //                             ? e($collection->description)
                //                             : '<em>No description available.</em>') // Payment description
                //                         .'<br>'
                //                         .'<small>Semester: '.e(optional($collection->semester)->semester ?? 'Unknown Semester').'</small>' // Add semester type
                //                     ),
                //                 ])
                //                 ->toArray())
                //             ->live()
                //             ->reactive()
                //             ->afterStateUpdated(function ($state, Set $set) {
                //                 // Keep the current state as is without clearing other selections
                //                 if (! is_array($state)) {
                //                     $set('collection_id', []);
                //                 }
                //             })
                //             ->columns(2)
                //             ->gridDirection('row'),
                //     ])
                //     ->columns(2),
                // Forms\Components\Section::make('Student School Year')
                //     ->relationship('enrollments')
                //     ->schema([
                //         Forms\Components\Select::make('schoolyear_id')
                //             ->label('School Year')
                //             ->options(Schoolyear::all()->pluck('schoolyear', 'id'))
                //             ->preload()
                //             ->searchable()
                //             ->live()
                //             ->reactive()
                //             ->afterStateUpdated(function ($state, Set $set) {
                //                 $set('semester_id', []);
                //                 $set('collection_id', []);
                //             })
                //             ->required(),
                // Forms\Components\CheckboxList::make('semester_id')
                //     ->label('Semester')
                //     ->inlineLabel()
                //     ->relationship('semesters', 'semester')
                //     ->options(fn (Get $get): array => Semester::query()
                //         ->where('schoolyear_id', $get('schoolyear_id'))
                //         ->pluck('semester', 'id')
                //         ->toArray())
                //     ->live()
                //     ->reactive()
                //     ->afterStateUpdated(function ($state, Set $set) {
                //         $set('collection_id', []);
                //     })
                //     ->columns(2)
                //     ->gridDirection('row'),
                //         CheckboxList::make('collection_id')
                //             ->label('Fee Type')
                //             ->inlineLabel()
                //             ->relationship('collections', 'amount') // Adjusted to match the relationship name and attribute in your model
                //             ->options(fn (Get $get): array => ModelsCollection::query()
                //                 ->whereIn('semester_id', $get('semester_id'))
                //                 ->get()
                //                 ->mapWithKeys(fn ($collection) => [
                //                     $collection->id => '₱'.number_format($collection->amount, 2), // Only amount here
                //                 ])
                //                 ->toArray())
                //             ->descriptions(fn (Get $get): array => ModelsCollection::query()
                //                 ->whereIn('semester_id', $get('semester_id'))
                //                 ->with('semester') // Eager load the semester relationship
                //                 ->get()
                //                 ->mapWithKeys(fn ($collection) => [
                //                     $collection->id => new HtmlString(
                //                         ($collection->description
                //                             ? e($collection->description)
                //                             : '<em>No description available.</em>') // Payment description
                //                         .'<br>'
                //                         .'<small>Semester: '.e(optional($collection->semester)->semester ?? 'Unknown Semester').'</small>' // Add semester type
                //                     ),
                //                 ])
                //                 ->toArray())
                //             ->live()
                //             ->reactive()
                //             ->afterStateUpdated(function ($state, Set $set) {
                //                 // Keep the current state as is without clearing other selections
                //                 if (! is_array($state)) {
                //                     $set('collection_id', []);
                //                 }
                //             })
                //             ->columns(2)
                //             ->gridDirection('row'),

                //     ])
                //     ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('studentidn')
                    ->label('Student IDN')
                    ->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->weight(FontWeight::Bold)
                    ->label('Last Name')
                    ->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('firstname')
                    ->label('First Name')
                    ->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('middlename')
                    ->label('Middle Name')
                    ->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'graduated' => 'gray',
                    })
                    ->searchable(),
                // TextColumn::make('enrollments.collections.amount')
                //     ->label('Total Amount')
                //     ->formatStateUsing(function ($record) {

                //         $total = $record->enrollments()
                //             ->with('collections')
                //             ->get()
                //             ->sum(function ($enrollment) {
                //                 return $enrollment->collections->sum('amount');
                //             });

                //         return '₱'.number_format($total, 2);
                //     }),
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
                SelectFilter::make('schoolyear')
                    ->relationship('enrollments.schoolyear', 'schoolyear')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                RelationManagerAction::make('enrollments-relation-manager')
                    ->label('View Enrollment')
                    ->color('primary')
                    ->disabledForm()
                    ->icon('heroicon-m-identification')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading('')
                    ->relationManager(EnrollmentsRelationManager::make()),
                RelationManagerAction::make('siblings-relation-manager')
                    ->label('Siblings')
                    ->color('cyan')
                    ->icon('heroicon-m-identification')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading('')
                    ->relationManager(SiblingRelationManager::make()),

                Tables\Actions\EditAction::make()
                    ->color('warning'),
                // DeleteAction::make()
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateHeading('No students yet')
            ->emptyStateDescription('Once you add students, it will appear here.');
    }

    public static function afterCreate(Stud $record): void
    {
        $studentId = $record->studentidn;
        $firstName = $record->firstname;
        $middleName = $record->middlename;
        $lastName = $record->lastname;
        $fullName = "{$firstName} {$middleName} {$lastName}";
        $email = "ptgea@{$studentId}";
        $role = "guest";
        $hashedPassword = Hash::make($studentId); // Hash the student ID for password

        // Check if the user already exists
        $existingUser = User::where('canId', $studentId)->first();

        if ($existingUser) {
            // Notify that the record already exists
            Notification::make()
                ->title('Duplicate Entry')
                ->body("A user with Student ID {$studentId} already exists.")
                ->danger()
                ->send();
        } else {
            // Insert the new user
            User::create([
                'firstname' => $firstName,
                'middlename' => $middleName,
                'lastname' => $lastName,
                'name' => $fullName,
                'email' => $email,
                'role' => $role,
                'canId' => $studentId,
                'password' => $hashedPassword, // Store hashed password
            ]);

            // Notify that the user was created
            Notification::make()
                ->title('User Created')
                ->body("User {$fullName} has been successfully created.")
                ->success()
                ->send();
        }
    }

    public static function saved(Stud $record): void
    {
        $studentId = $record->studentidn;
        $firstName = $record->firstname;
        $middleName = $record->middlename;
        $lastName = $record->lastname;
        $fullName = "{$firstName} {$middleName} {$lastName}";
        $email = "ptgea@{$studentId}";
        $role = "guest";
        $hashedPassword = Hash::make($studentId); // Hash the student ID for password

        // Check if the user already exists
        $existingUser = User::where('canId', $studentId)->first();

        if ($existingUser) {
            // Notify that the record already exists
            Notification::make()
                ->title('Duplicate Entry')
                ->body("A user with Student ID {$studentId} already exists.")
                ->danger()
                ->send();
        } else {
            // Insert the new user
            User::create([
                'firstname' => $firstName,
                'middlename' => $middleName,
                'lastname' => $lastName,
                'name' => $fullName,
                'email' => $email,
                'role' => $role,
                'canId' => $studentId,
                'password' => $hashedPassword, // Store hashed password
            ]);

            // Notify that the user was created
            Notification::make()
                ->title('User Created')
                ->body("User {$fullName} has been successfully created.")
                ->success()
                ->send();
        }
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EnrollmentsRelationManager::class,
            RelationManagers\SiblingRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStuds::route('/'),
            'create' => Pages\CreateStud::route('/create'),
            'edit' => Pages\EditStud::route('/{record}/edit'),
        ];
    }
}
