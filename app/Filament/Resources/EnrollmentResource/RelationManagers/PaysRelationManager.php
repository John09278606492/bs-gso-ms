<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use App\Models\Collection;
use App\Models\Pay;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Modal\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use stdClass;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use FontLib\Table\Type\name;
use Illuminate\Support\Facades\Log;

class PaysRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;

    protected static string $relationship = 'pays';

    protected static ?string $title = 'Payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        // Remaining Balance Display
                        Forms\Components\TextInput::make('balance')
                            ->label(
                                fn($livewire) => ($livewire->ownerRecord?->getBalanceAttribute() < 0)
                                    ? 'Refundable Balance'
                                    : 'Running Balance'
                            )
                            // ->prefixIcon('heroicon-m-peso-symbol')
                            ->default(
                                fn($livewire) => ($livewire->ownerRecord?->getBalanceAttribute() < 0)
                                    ? number_format(abs($livewire->ownerRecord?->getBalanceAttribute()), 2)
                                    : number_format($livewire->ownerRecord?->getBalanceAttribute() ?? 0, 2)
                            )
                            ->dehydrated(false)
                            ->disabled(),
                    ])
                    ->columnStart(1),

                Section::make('Transaction Details')
                    ->schema([
                        Grid::make()
                            ->schema([
                                CheckboxList::make('collections')
                                    ->label('Fund Type')
                                    // ->bulkToggleable()
                                    ->disableOptionWhen(function (string $value, Get $get) {
                                        $record = $this->ownerRecord;

                                        if (!$record) {
                                            return false;
                                        }

                                        // Get the IDs of the paid collections
                                        $paidIds = $record->collections()
                                            ->wherePivot('collection_status', 'paid')
                                            ->pluck('collection_id')
                                            ->toArray();

                                        // Disable the checkbox if the collection is paid
                                        return in_array((int) $value, $paidIds);
                                    })
                                    ->options(function ($livewire) {
                                        $record = $livewire->ownerRecord;

                                        if ($record) {
                                            return $record->collections()
                                                ->get()
                                                ->mapWithKeys(function ($collection) {
                                                    // Base description
                                                    $description = $collection->description . ' - ₱' . number_format($collection->amount, 2);

                                                    // If the collection status is "paid", mark it with green color
                                                    if ($collection->pivot->collection_status == 'paid') {
                                                        $description .= ' <span style="color: green;">(Paid)</span>';
                                                    }

                                                    // Return the collection ID with the formatted description
                                                    return [
                                                        $collection->id => new \Illuminate\Support\HtmlString($description),
                                                    ];
                                                })
                                                ->toArray();
                                        }

                                        return [];
                                    })
                                    ->descriptions(function ($livewire) {
                                        $record = $livewire->ownerRecord;

                                        if (!$record) {
                                            return [];
                                        }

                                        return $record->collections()
                                            ->with('semester')
                                            ->get()
                                            ->mapWithKeys(function ($collection) {
                                                return [
                                                    $collection->id => new \Illuminate\Support\HtmlString(
                                                        '<small><strong>Semester:</strong> ' . e(optional($collection->semester)->semester ?? 'N/A') . '</small>'
                                                    ),
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->reactive()
                                    ->afterStateHydrated(function (callable $set, $component, $state, $livewire, Get $get) {
                                        $record = $livewire->ownerRecord;

                                        if ($record) {
                                            // Get collection IDs with unpaid status
                                            $unpaidIds = $record->collections()
                                                ->wherePivot('collection_status', '!=', 'paid')
                                                ->pluck('collection_id')
                                                ->toArray();

                                            // Set only unpaid as selected
                                            $component->state($unpaidIds);

                                            // Calculate total from unpaid only
                                            $total = $record->collections()
                                                ->whereIn('collection_id', $unpaidIds)
                                                ->sum('amount');

                                            $balance = $get('balance');

                                            // Convert balance and amount to numeric values
                                            $numericBalance = (float) str_replace([',', '₱'], '', $balance);

                                            $formatted = number_format($total, 2, '.', ',');
                                            $amountDeducted = min($formatted, $numericBalance);
                                            $set('amount', $amountDeducted);
                                        }
                                    })
                                    ->afterStateUpdated(function (callable $set, $state, $livewire, Get $get) {
                                        $record = $livewire->ownerRecord;

                                        $set('payment_summary', 'No payment entered yet.');

                                        if ($record) {
                                            $total = $record->collections()->whereIn('collection_id', $state)->sum('amount');
                                            $formatted = number_format($total, 2, '.', ',');

                                            $balance = $get('balance');

                                            // Convert balance and amount to numeric values
                                            $numericBalance = (float) str_replace([',', '₱'], '', $balance);

                                            $formatted = number_format($total, 2, '.', ',');
                                            $amountDeducted = min($formatted, number_format($numericBalance, 2, '.', ','));
                                            $set('amount', $amountDeducted);
                                        }
                                    })
                                    ->columns(1),


                            ])
                            ->columnStart(1),

                        // Amount Input
                        Forms\Components\TextInput::make('amount')
                            ->mask(RawJs::make('$money($input)'))
                            ->required()
                            ->disabled()
                            ->label('Total Amount Due')
                            ->default(function ($livewire) {
                                $record = $livewire->ownerRecord;

                                if ($record) {
                                    // Get only unpaid collections
                                    $unpaidIds = $record->collections()
                                        ->wherePivot('collection_status', '!=', 'paid')
                                        ->pluck('collection_id')
                                        ->toArray();

                                    $total = $record->collections()->whereIn('collection_id', $unpaidIds)->sum('amount');
                                    return number_format($total, 2, '.', ',');
                                }

                                return number_format(0, 2, '.', ',');
                            })
                            ->stripCharacters(',')
                            ->extraAttributes([
                                'class' => 'text-red-600 font-bold'
                            ])
                            ->extraInputAttributes([
                                'onInput' => 'this.value = this.value.replace(/[^\d.]/g, "").replace(/(\..*?)\.+/g, "$1").replace(/\B(?=(\d{3})+(?!\d))/g, ",")',
                            ])
                            ->numeric()
                            // ->prefixIcon('heroicon-m-peso-symbol')
                            ->rules([
                                function () {
                                    return function ($attribute, $value, $fail) {
                                        $parentModel = $this->getRelationship()->getParent();

                                        if (method_exists($parentModel, 'getBalanceAttribute')) {
                                            $balance = $parentModel->getBalanceAttribute();
                                            $numericBalance = (float) str_replace([',', '₱'], '', $balance);

                                            if ($numericBalance <= 0) {
                                                $fail('Fully Paid! Remaining balance is: ₱' . number_format($numericBalance, 2));
                                            }
                                        } else {
                                            $fail('Balance validation failed due to missing method.');
                                        }
                                    };
                                },
                            ])
                            ->dehydrateStateUsing(function ($state) {
                                $parentModel = $this->getRelationship()->getParent();

                                if (method_exists($parentModel, 'getBalanceAttribute')) {
                                    $balance = $parentModel->getBalanceAttribute();
                                    $numericBalance = (float) str_replace([',', '₱'], '', $balance);

                                    if ($numericBalance > 0) {
                                        return min((float) str_replace(',', '', $state), $numericBalance);
                                    }
                                }
                                return (float) str_replace(',', '', $state);
                            }),
                        Forms\Components\TextInput::make('amount_tendered')
                            ->label('Tender Amount')
                            ->placeholder('0.00')
                            // ->prefixIcon('heroicon-m-peso-symbol')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->required()
                            ->extraInputAttributes([
                                'style' => "color:red;font-weight:bold;"
                            ])
                            ->dehydrateStateUsing(function ($state) {
                                return (float) str_replace(',', '', $state);
                            })
                            ->rules([
                                function () {
                                    return function ($attribute, $value, $fail) {
                                        $tendered = (float) str_replace(',', '', $value);

                                        $parentModel = $this->getRelationship()->getParent();

                                        if ($parentModel) {
                                            $collectionIds = $parentModel->collections->pluck('collection_id')->toArray();
                                            $totalDue = $parentModel->collections()->whereIn('collection_id', $collectionIds)->sum('amount');

                                            if ($tendered < $totalDue) {
                                                $fail('Amount tendered must be greater than or equal to the total amount due (₱' . number_format($totalDue, 2) . ').');
                                            }
                                        }
                                    };
                                },
                            ]),

                        // Payment Summary Display
                        Forms\Components\Textarea::make('payment_summary')
                            ->label('Transaction Summary')
                            ->default('No payment entered yet.')
                            ->reactive()
                            ->disabled()
                            ->autosize()
                            ->dehydrated(false),
                    ])
                    ->footerActions([
                        // Pay Button - Updates Payment Summary
                        ActionsAction::make('pay')
                            ->label('Pay')
                            ->color('primary')
                            ->action(
                                function (
                                    callable $set,
                                    callable $get,
                                    $livewire
                                ) {
                                    $amount = $get('amount');
                                    $amount_tendered = $get('amount_tendered');

                                    $balance = $livewire->ownerRecord?->getBalanceAttribute() ?? '₱0.00';
                                    $numericBalance = (float) str_replace([',', '₱'], '', $balance);

                                    // Prevent payment if balance is already zero or negative
                                    if ($numericBalance <= 0) {
                                        Notification::make()
                                            ->title('Payment Not Allowed')
                                            ->body('The student is fully paid! No additional payment required.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    // Ensure amount is entered
                                    if (empty($amount) || (float) str_replace(',', '', $amount) <= 0) {
                                        Notification::make()
                                            ->title('No Fund Type selected')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    if ((float) str_replace(',', '', $amount_tendered) < (float) str_replace(',', '', $amount)) {
                                        Notification::make()
                                            ->title('Amount Tendered is less than Total Amount Due or empty')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    // Get balance and convert it to a numeric value

                                    // Proceed to update the payment summary
                                    $livewire->updatePaymentSummary($amount_tendered, $amount, $set, $livewire);
                                }
                            ),

                        ActionsAction::make('confirm_payment')
                            ->label('Confirm Payment')
                            ->color('success')
                            ->requiresConfirmation()
                            ->hidden(fn(callable $get) => empty($get('payment_summary')) || $get('payment_summary') === 'No payment entered yet.')
                            ->action(function (callable $set, callable $get, $livewire) {
                                $amount = $get('amount');
                                $balance = $get('balance');
                                $amount_tendered = $get('amount_tendered');

                                // Convert balance and amount to numeric values
                                $numericBalance = (float) str_replace([',', '₱'], '', $balance);
                                $numericAmount = (float) str_replace(',', '', $amount);
                                $numericAmountTendered = (float) str_replace(',', '', $amount_tendered);

                                // ✅ Check if the entered amount is valid
                                if (empty($amount) || $numericAmountTendered <= 0) {
                                    Notification::make()
                                        ->title('Invalid Amount')
                                        ->body('Please enter a valid amount.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                // ✅ Determine the amount to be deducted (prevents overpayment)
                                $amountDeducted = min($numericAmountTendered, $numericAmount);
                                $remainingBalance = max(0, $numericBalance - $amountDeducted);
                                $change = max(0, $numericAmount - $numericBalance);

                                // ✅ Get the parent enrollment
                                $enrollment = $livewire->getRelationship()->getParent();
                                if (!$enrollment) {
                                    logger()->error('No related enrollment found for payment record.');
                                    Notification::make()
                                        ->title('Error')
                                        ->body('No related enrollment found.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                // ✅ Save the payment with the **correct deducted amount**
                                $payment = Pay::create([
                                    'enrollment_id' => $enrollment->id,
                                    'amount' => $amountDeducted, // 💡 Only deduct the allowed amount
                                    'status1' => 'paid',
                                ]);

                                $selectedCollections = $get('collections');

                                if (is_array($selectedCollections)) {
                                    foreach ($selectedCollections as $collectionId) {
                                        $updated = DB::table('collection_enrollment')
                                            ->where('enrollment_id', $enrollment->id) // or $enrollment->id, depending on your actual variable
                                            ->where('collection_id', $collectionId)
                                            ->update([
                                                'collection_status' => 'paid',
                                                'updated_at' => now(), // optional: update the timestamp too
                                            ]);

                                        // Optional: debug
                                        if ($updated === 0) {
                                            logger("No record updated for enrollment_id: {$payment->id}, collection_id: {$collectionId}");
                                        }
                                    }
                                }

                                if (!$payment) {
                                    logger()->error('Failed to save payment record.');
                                    Notification::make()
                                        ->title('Payment Error')
                                        ->body('Payment could not be saved. Please try again.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                // ✅ Find the recipient (user) based on canId
                                $recipient = User::where('canId', $enrollment->stud->studentidn)->first();
                                if ($recipient) {
                                    Notification::make()
                                        ->title('Payment Received')
                                        ->body("Thank you for paying ₱" . number_format($amountDeducted, 2, '.', ',') .
                                            ". Your remaining balance is ₱" . number_format($remainingBalance, 2))
                                        ->success()
                                        ->sendToDatabase($recipient, isEventDispatched: true);
                                } else {
                                    logger()->error("No user found with canId: {$enrollment->stud->studentidn}");
                                }

                                // ✅ Update enrollment status if fully paid
                                if ($remainingBalance <= 0) {
                                    DB::table('enrollments')
                                        ->where('id', $enrollment->id)
                                        ->update(['status' => 'paid']);

                                    logger()->info('Payment is correct and balance is zero or below.');
                                } else {
                                    logger()->info("Remaining balance: {$remainingBalance}");
                                }

                                // ✅ Clear input fields
                                $set('amount', '');
                                $set('tendered_amount', '');
                                $set('collections', []);
                                $set('payment_summary', 'No payment entered yet.');
                                $set('balance', $livewire->ownerRecord?->getBalanceAttribute() ?? '₱0.00');

                                // ✅ Show success notification
                                Notification::make()
                                    ->title('Payment Successfully Processed')
                                    ->success()
                                    ->send();
                            })
                    ]),
                Forms\Components\TextInput::make('status')
                    ->readOnly()
                    ->hidden()
                    ->default('paid'),
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
                Tables\Columns\TextColumn::make('status1')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'refunded' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date/Time')
                    ->dateTime('M d, Y h:i a')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Date/Time Paid Updated')
                    ->dateTime('M d, Y h:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('return_payment')
                    ->label('Refund')
                    ->modalHeading('Refund Form')
                    ->color('warning')
                    ->closeModalByClickingAway(false)
                    ->icon('heroicon-m-arrow-path')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalSubmitActionLabel('Pay')
                    ->visible(
                        fn($livewire) => ($livewire->ownerRecord?->getBalanceAttribute() < 0)
                    )
                    ->form([
                        Forms\Components\TextInput::make('refundable_amount')
                            ->label('Refundable Amount')
                            ->dehydrated(false)
                            ->default(
                                fn($livewire) =>
                                number_format(abs((float) str_replace(
                                    [',', '₱'],
                                    '',
                                    $livewire->ownerRecord?->getBalanceAttribute()
                                )), 2)
                            )
                            ->prefixIcon('heroicon-m-peso-symbol')
                            ->disabled(),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('confirm_refund')
                                ->label('Confirm Refund')
                                ->requiresConfirmation()
                                ->hidden(fn($livewire) => ($livewire->ownerRecord?->getBalanceAttribute() == 0))
                                ->action(function (
                                    $livewire,
                                    callable $set,
                                    callable $get,
                                ) {
                                    $parentModel = $livewire->getRelationship()->getParent();

                                    if ($parentModel) {
                                        $balance = $parentModel->getBalanceAttribute();
                                        $numericBalance = (float) str_replace([',', '₱'], '', $balance);

                                        // $bal = (float) str_replace([',', '₱'], '', $get('refundable_amount'));

                                        if ($numericBalance == 0) {
                                            Notification::make()
                                                ->title('Amount already refunded')
                                                ->body('The student has already receieved the refunded amount!')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        if ($numericBalance < 0) {
                                            // Insert refund record
                                            DB::table('pays')->insert([
                                                'enrollment_id' => $parentModel->id,
                                                'amount' => abs($numericBalance),
                                                'status1' => 'refunded',
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]);

                                            // Send refund notification
                                            $recipient = User::where('canId', $parentModel->stud->studentidn)->first();

                                            if ($recipient) {
                                                Notification::make()
                                                    ->title('Refund Processed')
                                                    ->body("A refund of ₱" . number_format(abs($numericBalance), 2) . " has been successfully processed.")
                                                    ->success()
                                                    ->sendToDatabase($recipient, isEventDispatched: true);
                                            } else {
                                                logger()->error("No user found with canId: {$parentModel->stud->studentidn}");
                                            }

                                            logger()->info("Refund Processed: ₱" . number_format(abs($numericBalance), 2));
                                            $set('refundable_amount', $livewire->ownerRecord?->getRefundableAmountAttribute() ?? '₱0.00');

                                            // ✅ Show success notification
                                            Notification::make()
                                                ->title('Refund Successfully Processed')
                                                ->success()
                                                ->send();
                                        }
                                    }
                                })
                                ->color('danger')
                                ->icon('heroicon-m-exclamation-triangle'),
                        ]),
                    ])
                    ->disableCreateAnother()
                    ->after(function (Pay $record) {
                        $parentModel = $record->enrollment;

                        if ($parentModel) {
                            $balance = $parentModel->getBalanceAttribute();
                            $numericBalance = str_replace([',', '₱'], '', $balance);

                            // Find the recipient (user) based on canId
                            $recipient = User::where('canId', $record->enrollment->stud->studentidn)->first();

                            if ($recipient) {
                                // Send notification to the correct user
                                Notification::make()
                                    ->title('Payment Received')
                                    ->body("Thank you for paying the amount of ₱" . number_format($record->amount, 2, '.', ',') .
                                        ". Your remaining balance is ₱{$balance}.")
                                    ->success()
                                    ->sendToDatabase($recipient, isEventDispatched: true);
                            } else {
                                logger()->error("No user found with canId: {$record->enrollment->stud->studentidn}");
                            }

                            // Update enrollment status if fully paid
                            if ((float) $numericBalance <= 0) {
                                DB::table('enrollments')
                                    ->where('id', $this->getOwnerRecord()->id)
                                    ->update(['status' => 'paid']);

                                logger()->info('Payment is correct and balance is zero or below.');
                            } else {
                                logger()->info("Remaining balance: {$numericBalance}");
                            }
                        } else {
                            logger()->error('No related enrollment found for payment record.');
                        }
                    }),
                Tables\Actions\CreateAction::make('create_payment')
                    ->label('New payment')
                    ->modalHeading('Payment Form')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalSubmitActionLabel('Pay')
                    ->visible(
                        fn($livewire) => ($livewire->ownerRecord?->getBalanceAttribute() > 0)
                    )
                    ->disableCreateAnother()
                    ->after(function (Pay $record) {
                        $parentModel = $record->enrollment;

                        if ($parentModel) {
                            $balance = $parentModel->getBalanceAttribute();
                            $numericBalance = str_replace([',', '₱'], '', $balance);

                            // Find the recipient (user) based on canId
                            $recipient = User::where('canId', $record->enrollment->stud->studentidn)->first();

                            if ($recipient) {
                                // Send notification to the correct user
                                Notification::make()
                                    ->title('Payment Received')
                                    ->body("Thank you for paying the amount of ₱" . number_format($record->amount, 2, '.', ',') .
                                        ". Your remaining balance is ₱{$balance}.")
                                    ->success()
                                    ->sendToDatabase($recipient, isEventDispatched: true);
                            } else {
                                logger()->error("No user found with canId: {$record->enrollment->stud->studentidn}");
                            }

                            // Update enrollment status if fully paid
                            if ((float) $numericBalance <= 0) {
                                DB::table('enrollments')
                                    ->where('id', $this->getOwnerRecord()->id)
                                    ->update(['status' => 'paid']);

                                logger()->info('Payment is correct and balance is zero or below.');
                            } else {
                                logger()->info("Remaining balance: {$numericBalance}");
                            }
                        } else {
                            logger()->error('No related enrollment found for payment record.');
                        }
                    })
            ])
            ->actions([
                // Tables\Actions\Action::make('Generate Receipt')
                //     ->icon('heroicon-o-document')
                //     ->action(function (Pay $record) {
                //         $folderPath = storage_path('app/public/temp_receipts');
                //         if (! File::exists($folderPath)) {
                //             File::makeDirectory($folderPath, 0777, true, true);
                //         }
                //         $pdf = Pdf::loadView('receipts.payment', [
                //             'id' => $record->id,
                //             'amount_formatted' => 'PHP ' . number_format($record->amount, 2),
                //             'status' => $record->status,
                //             'date' => $record->created_at->format('M. d, Y g:i a'),
                //             'enrollment' => $record->enrollment->stud->only(['id', 'lastname', 'firstname', 'middlename']),
                //         ])
                //             ->setOption('encoding', 'UTF-8');
                //         $pdfOutput = $pdf->output();
                //         $filePath = $folderPath . '/receipt-' . $record->id . '.pdf';
                //         file_put_contents($filePath, $pdfOutput);
                //         $publicFilePath = asset('storage/temp_receipts/receipt-' . $record->id . '.pdf');
                //         $jsCode = "
                //         window.open('{$publicFilePath}', '_blank');
                //     ";

                //         return $this->js($jsCode);
                //     }),
                Tables\Actions\EditAction::make()
                    ->hidden(),
                // Tables\Actions\DeleteAction::make()
                //     ->label('Return')
                //     ->color('warning')
                //     ->icon('heroicon-m-arrow-path')
                //     ->after(function (Pay $record) {
                //         $parentModel = $record->enrollment;

                //         if ($parentModel) {
                //             $balance = $parentModel->getBalanceAttribute();

                //             $numericBalance = str_replace([',', '₱'], '', $balance);

                //             if ((float) $numericBalance <= 0) {
                //                 DB::table('enrollments')
                //                     ->where('id', $this->getOwnerRecord()->id)
                //                     ->update(['status' => 'paid']);
                //                 logger()->info('Payment is correct and balance is zero or below.');
                //             } else {
                //                 DB::table('enrollments')
                //                     ->where('id', $this->getOwnerRecord()->id)
                //                     ->update(['status' => NULL]);
                //                 logger()->info("Remaining balance: {$numericBalance}");
                //             }
                //         } else {
                //             logger()->error('No related enrollment found for payment record.');
                //         }
                //     }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()
                //         ->after(function ($records, Get $amount) { // `$records` is a collection of selected records
                //             foreach ($records as $record) {
                //                 $parentModel = $record->enrollment;

                //                 if ($parentModel) {
                //                     $balance = $parentModel->getBalanceAttribute();

                //                     $numericBalance = str_replace([',', '₱'], '', $balance);

                //                     if ((float) $numericBalance <= 0) {
                //                         DB::table('enrollments')
                //                             ->where('id', $parentModel->id) // Use the `enrollment` model's ID
                //                             ->update(['status' => 'paid']);
                //                         logger()->info("Payment is correct for enrollment ID {$parentModel->id}, balance is zero or below.");
                //                     } else {
                //                         DB::table('enrollments')
                //                             ->where('id', $parentModel->id) // Use the `enrollment` model's ID
                //                             ->update(['status' => NULL]);
                //                         logger()->info("Remaining balance for enrollment ID {$parentModel->id}: {$numericBalance}");
                //                     }
                //                 } else {
                //                     logger()->error("No related enrollment found for payment record ID {$record->id}.");
                //                 }
                //             }
                //         }),
                // ]),
            ])

            ->heading('Payment history')
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Once student pays, it will appear here.');
        // ->save(function (Forms\ComponentContainer $form, Pay $record) {
        //     $enrollment = $record->enrollment;

        //     if ($enrollment && $enrollment->getBalanceAttribute() <= 0) {
        //         $enrollment->update(['status' => 'paid']);
        //     }
        // });
    }

    public function updatePaymentSummary($amountTendered, $amount, callable $set, $livewire)
    {
        $balance = (float) str_replace([',', '₱'], '', $livewire->ownerRecord?->getBalanceAttribute() ?? '0');
        $amount = (float) str_replace([',', '₱'], '', $amount);
        $amountTendered = (float) str_replace(',', '', $amountTendered);

        $amountDeducted = min($amount, $amountTendered);
        $remainingBalance = max(0, $balance - $amountTendered);
        $change = max(0, $amountTendered - $amount);

        $summary = "Amount Tendered: ₱" . number_format($amountTendered, 2) . "\n" .
            "Amount Deducted: ₱" . number_format($amountDeducted, 2) . "\n" .
            "Running Balance: ₱" . number_format($remainingBalance, 2) . "\n" .
            "Change: ₱" . number_format($change, 2);

        $set('payment_summary', $summary);
    }

    public function handlePayment($data, callable $set)
    {
        // Log the payment for debugging
        Log::info('Payment processed: ', $data);

        // Reset the form fields after payment
        $set('amount', '');
        $set('payment_summary', 'No payment entered yet.');

        Notification::make()
            ->title('Payment successfully processed')
            ->body('Thank you for your payment.')
            ->success()
            ->send();
    }

    protected function afterSave(): void
    {
        $enrollment = $this->getOwnerRecord(); // Retrieve the parent Enrollment

        if ($enrollment) {
            $balance = $enrollment->getBalanceAttribute();

            if ($balance <= 0) {
                logger()->info('Should Update');
                $enrollment->update(['status' => 'paid']);
            } else {
                logger()->info("Balance is not zero or below: {$balance}");
            }
        } else {
            logger()->error('No related enrollment found for payment record.');
        }
    }
}
