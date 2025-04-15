<?php

namespace App\Jobs;

use App\Models\Stud;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ManageStudentAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Find or create the student record
        $students = Stud::firstOrNew([
            'studentidn' => $this->data['studentidn'],
        ]);

        // Update student details
        $students->firstname = $this->data['firstname'] ?? $students->firstname;
        $students->middlename = $this->data['middlename'] ?? $students->middlename;
        $students->lastname = $this->data['lastname'] ?? $students->lastname;

        // Prepare user details
        $studentIDN = $students->studentidn;
        $fullName = trim($students->firstname.' '.$students->middlename.' '.$students->lastname);
        $studentEmail = 'ptgea'.'@'.$students->studentidn;
        $hashedPassword = Hash::make($studentIDN);

        // Log the details
        Log::info('Processing Student Record', [
            'studentidn' => $studentIDN,
            'fullName' => $fullName,
            'email' => $studentEmail,
        ]);

        // Check if the account exists in the users table
        $existingAccount = DB::table('users')->where('canId', $studentIDN)->exists();

        if ($existingAccount) {
            // Update the existing record
            DB::table('users')->where('canId', $studentIDN)
                ->update([
                    'name' => $fullName,
                    'email' => $studentEmail,
                    'role' => 'guest',
                    'password' => $hashedPassword,
                ]);

            Log::info('User Account Updated', ['canId' => $studentIDN]);
        } else {
            // Insert a new user record
            DB::table('users')->insert([
                'name' => $fullName,
                'email' => $studentEmail,
                'canId' => $studentIDN,
                'role' => 'guest',
                'password' => $hashedPassword,
            ]);

            Log::info('New User Account Created', ['canId' => $studentIDN]);
        }

        // Save the student record
        $students->save();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has been completed.';

        return $body;
    }
}
