<?php

namespace App\Filament\Resources\StudResource\Pages;

use App\Filament\Resources\StudResource;
use App\Models\User;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Action;
use Illuminate\Support\Str;


class CreateStud extends CreateRecord
{
    // use HasWizard;

    protected static string $resource = StudResource::class;

    protected static bool $canCreateAnother = false;

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

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Submit'))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Add Student');
    }

    private function sanitizeName(string $name): string
    {
        // Convert to ASCII (e.g., "ñ" → "n", "é" → "e")
        $name = Str::ascii($name);

        // Remove non-alphabetic characters (keeps only a-z and A-Z)
        $name = preg_replace('/[^a-zA-Z]/', '', $name);

        // Convert to lowercase
        return strtolower($name);
    }

    protected function afterCreate(): void
    {
        $studentId = $this->record->studentidn;
        $firstName = $this->record->firstname;
        $middleName = $this->record->middlename;
        $lastName = $this->record->lastname;
        $fullName = "{$firstName} {$middleName} {$lastName}";
        $email1 = "ptgea@{$studentId}";
        $role = "guest";
        $hashedPassword = Hash::make($studentId); // Hash the student ID for password
        $firstName = $this->sanitizeName($firstName ?? '');
        $lastName = $this->sanitizeName($lastName ?? '');

        // Create email1 using the convention firstname.lastname@bisu.edu.ph
        $email = strtolower($firstName . '.' . $lastName . '@bisu.edu.ph');

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
}
