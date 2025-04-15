<?php

namespace App\Filament\Resources\StudResource\RelationManagers;

use App\Models\Collection as ModelsCollection;
use App\Models\College;
use App\Models\Program;
use App\Models\Schoolyear;
use App\Models\Semester;
use App\Models\Yearlevel;
use App\Models\Yearlevelpayments;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use stdClass;

class EnrollmentsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;

    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Academic Info';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->enrollments()->count();

        return $count > 0 ? $count : 0;
    }

    protected static ?string $badgeTooltip = 'Number of enrollment';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Academic Information')
                    ->schema([
                        Forms\Components\TextInput::make('stud_id')
                            ->hidden()
                            ->reactive()
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->id),
                        Forms\Components\Select::make('college_id')
                            ->label('College')
                            ->options(College::all()->pluck('college', 'id'))
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set) {
                                $set('program_id', null);
                                $set('yearlevel_id', null);
                                $set('yearlevelpayment_id', []);
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('program_id')
                            ->label('Program')
                            ->options(fn(Get $get): Collection => Program::query()
                                ->where('college_id', $get('college_id'))
                                ->pluck('program', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set) {
                                $set('yearlevel_id', null);
                                $set('yearlevelpayment_id', []);
                            })
                            ->required(),
                        Forms\Components\Select::make('yearlevel_id')
                            ->label('Year Level')
                            ->options(fn(Get $get): Collection => Yearlevel::query()
                                ->where('program_id', $get('program_id'))
                                ->pluck('yearlevel', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn(Set $set) => $set('yearlevelpayment_id', []))
                            ->required(),
                        CheckboxList::make('yearlevelpayment_id')
                            ->label('Year Level Fee Type')
                            ->inlineLabel()
                            ->hidden()
                            ->relationship('yearlevelpayments', 'amount')
                            ->options(fn(Get $get): array => Yearlevelpayments::query()
                                ->where('yearlevel_id1', $get('yearlevel_id'))
                                ->get()
                                ->mapWithKeys(fn($payment) => [
                                    $payment->id => '₱' . number_format($payment->amount, 2),
                                ])
                                ->toArray())
                            ->descriptions(fn(Get $get): array => Yearlevelpayments::query()
                                ->where('yearlevel_id1', $get('yearlevel_id'))
                                ->get()
                                ->mapWithKeys(fn($payment) => [
                                    $payment->id => new HtmlString(
                                        $payment->description
                                            ? e($payment->description)
                                            : '<em>No description available.</em>'
                                    ),
                                ])
                                ->toArray())
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! is_array($state)) {
                                    $set('yearlevelpayments', []);
                                }
                            }),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('schoolyear_id')
                                    ->label('School Year')
                                    ->options(Schoolyear::all()->pluck('schoolyear', 'id'))
                                    ->preload()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('semester_id', []);
                                        $set('collection_id', []);
                                    })
                                    ->required()
                                    ->unique(
                                        modifyRuleUsing: function (Unique $rule, callable $get) {
                                            $studId = $get('stud_id');
                                            $collegeId = $get('college_id');
                                            $programId = $get('program_id');
                                            $yearlevelId = $get('yearlevel_id');
                                            $schoolyearId = $get('schoolyear_id');

                                            return $rule
                                                ->where('stud_id', $studId)
                                                ->where('college_id', $collegeId)
                                                ->where('program_id', $programId)
                                                ->where('yearlevel_id', $yearlevelId)
                                                ->where('schoolyear_id', $schoolyearId);
                                        },
                                        ignoreRecord: true,
                                    )
                                    ->validationMessages([
                                        'unique' => 'Student already enrolled in this school year',
                                    ]),
                            ])
                            ->columnStart(1),
                        Forms\Components\CheckboxList::make('semester_id')
                            ->label('Semester')
                            ->inlineLabel()
                            ->relationship('semesters', 'semester')
                            ->options(fn(Get $get): array => Semester::query()
                                ->where('schoolyear_id', $get('schoolyear_id'))
                                ->pluck('semester', 'id')
                                ->toArray())
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('collection_id', []);
                            })
                            ->columns(2)
                            ->gridDirection('row'),
                        CheckboxList::make('collection_id')
                            ->label('Semester Fee Type')
                            ->inlineLabel()
                            ->relationship('collections', 'amount') // Adjusted to match the relationship name and attribute in your model
                            ->options(fn(Get $get): array => ModelsCollection::query()
                                ->whereIn('semester_id', $get('semester_id'))
                                ->get()
                                ->mapWithKeys(fn($collection) => [
                                    $collection->id => '₱' . number_format($collection->amount, 2), // Only amount here
                                ])
                                ->toArray())
                            ->descriptions(fn(Get $get): array => ModelsCollection::query()
                                ->whereIn('semester_id', $get('semester_id'))
                                ->with('semester') // Eager load the semester relationship
                                ->get()
                                ->mapWithKeys(fn($collection) => [
                                    $collection->id => new HtmlString(
                                        ($collection->description
                                            ? e($collection->description)
                                            : '<em>No description available.</em>') // Payment description
                                            . '<br>'
                                            . '<small>Semester: ' . e(optional($collection->semester)->semester ?? 'Unknown Semester') . '</small>' // Add semester type
                                    ),
                                ])
                                ->toArray())
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('schoolyear')
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
                Tables\Columns\TextColumn::make('schoolyear.schoolyear'),
                Tables\Columns\TextColumn::make('college.college'),
                Tables\Columns\TextColumn::make('program.program'),
                Tables\Columns\TextColumn::make('yearlevel.yearlevel'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disableCreateAnother()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Student successfully enrolled!'),
                    )
                    ->failureNotification(
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->title('Failed to enroll student.'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabledForm()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalHeading('View Enrollment')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Enrollment updated successfully!'),
                    )
                    ->failureNotification(
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->title('Failed to update enrollment.'),
                    ),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Enrollment')
                    ->modalDescription('Are you sure you\'d like to delete this enrollment record? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete enrollment')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Enrollment deleted successfully!'),
                    )
                    ->failureNotification(
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->title('Failed to delete enrollment.'),
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Enrollments')
                        ->modalDescription('Are you sure you\'d like to delete these enrollment records? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete enrollments')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->color('success')
                                ->icon('heroicon-o-check-circle')
                                ->title('Enrollments deleted successfully!'),
                        )
                        ->failureNotification(
                            Notification::make()
                                ->danger()
                                ->color('danger')
                                ->icon('heroicon-o-x-circle')
                                ->title('Failed to delete enrollments.'),
                        ),
                ]),
            ])
            ->emptyStateHeading('No enrollments found.');
    }
}
