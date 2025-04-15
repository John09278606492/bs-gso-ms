<?php

namespace App\Filament\Resources\StudResource\Pages;

use App\Filament\Resources\StudResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Js;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Forms\Components\Wizard\Step;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditStud extends EditRecord
{
    // use HasWizard;

    protected static string $resource = StudResource::class;

    // protected function getSteps(): array
    // {
    //     return [
    //         Step::make('Student Personal Information')
    //             ->icon('heroicon-m-user-circle')
    //             ->schema(StudResource::getPersonalInformation()),
    //         Step::make('Student Academic Information')
    //             ->icon('heroicon-m-academic-cap')
    //             ->schema(StudResource::getAcademicInformation()),
    //     ];
    // }

    private function sanitizeName(string $name): string
    {
        // Convert to ASCII (e.g., "ñ" → "n", "é" → "e")
        $name = Str::ascii($name);

        // Remove non-alphabetic characters (keeps only a-z and A-Z)
        $name = preg_replace('/[^a-zA-Z]/', '', $name);

        // Convert to lowercase
        return strtolower($name);
    }

    protected function afterSave(): void
    {
        $studentId = $this->record->studentidn;
        $firstName = $this->record->firstname;
        $middleName = $this->record->middlename;
        $lastName = $this->record->lastname;
        $fullName = "{$firstName} {$middleName} {$lastName}";
        $email1 = "ptgea@{$studentId}";
        $role = "guest";
        $hashedPassword = Hash::make($studentId); // Hash the student ID for password

        $hashedPassword = Hash::make($studentId); // Hash the student ID for password
        $firstName = $this->sanitizeName($firstName ?? '');
        $lastName = $this->sanitizeName($lastName ?? '');

        // Create email1 using the convention firstname.lastname@bisu.edu.ph
        $email = strtolower($firstName . '.' . $lastName . '@bisu.edu.ph');

        // Check if the user already exists
        $existingUser = User::where('canId', $studentId)->first();

        if ($existingUser) {
            User::where('canId', $studentId)->update([
                'firstname' => $this->record->firstname,
                'middlename' => $this->record->middlename,
                'lastname' => $this->record->lastname,
                'name' => $fullName,
                'email' => $email,
                'email1' => $email1,
                'role' => $role,
                'canId' => $studentId,
                'password' => $hashedPassword,
            ]);

            // Notify that the user was created
            Notification::make()
                ->title('User Updated')
                ->body("User {$fullName} has been successfully updated.")
                ->success()
                ->send();
        } else {
            User::create([
                'firstname' => $this->record->firstname,
                'middlename' => $this->record->middlename,
                'lastname' => $this->record->lastname,
                'name' => $fullName,
                'email' => $email,
                'email1' => $email1,
                'role' => $role,
                'canId' => $studentId,
                'password' => $hashedPassword,
            ]);

            // Notify that the user was created
            Notification::make()
                ->title('User Created')
                ->body("User {$fullName} has been successfully created.")
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('return')
                ->color('primary')
                ->icon('heroicon-m-arrow-left-circle')
                ->label('Go back')
                ->livewireClickHandlerEnabled()
                ->url($this->previousUrl ?? $this->getResource()::getUrl('index')),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('Edit Student Information');
    }

    // protected function afterSaved(): void
    // {
    //     $studentId = $this->record->studentidn;
    //     $firstName = $this->record->firstname;
    //     $middleName = $this->record->middlename;
    //     $lastName = $this->record->lastname;
    //     $fullName = "{$firstName} {$middleName} {$lastName}";
    //     $email = "ptgea@{$studentId}";
    //     $role = "guest";
    //     $hashedPassword = Hash::make($studentId); // Hash the student ID for password

    //     // Check if the user already exists
    //     $existingUser = User::where('canId', $studentId)->first();

    //     if ($existingUser) {
    //         // Notify that the record already exists
    //         Notification::make()
    //             ->title('Duplicate Entry')
    //             ->body("A user with Student ID {$studentId} already exists.")
    //             ->danger()
    //             ->send();
    //     } else {
    //         // Insert the new user
    //         User::create([
    //             'firstname' => $firstName,
    //             'middlename' => $middleName,
    //             'lastname' => $lastName,
    //             'name' => $fullName,
    //             'email' => $email,
    //             'role' => $role,
    //             'canId' => $studentId,
    //             'password' => $hashedPassword, // Store hashed password
    //         ]);

    //         // Notify that the user was created
    //         Notification::make()
    //             ->title('User Created')
    //             ->body("User {$fullName} has been successfully created.")
    //             ->success()
    //             ->send();
    //     }
    // }

    // protected function getSaveFormAction(): Action
    // {
    //     return Action::make('save')
    //         ->label(__('Update'))
    //         ->submit('save')
    //         ->keyBindings(['mod+s']);
    // }

    // protected function getCancelFormAction(): Action
    // {
    //     return Action::make('cancel')
    //         ->label(__('Close'))
    //         ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = '.Js::from($this->previousUrl ?? static::getResource()::getUrl()).')')
    //         ->color('gray');
    // }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function getContentTabLabel(): ?string
    {
        return 'Student Info';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
