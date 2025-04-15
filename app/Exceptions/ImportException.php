<?php

namespace App\Exceptions;

use Exception;
use Filament\Notifications\Notification;

class ImportException extends Exception
{
    public function report()
    {
        // No need to do anything here, the notification is handled in the importer.
    }

    public function render($request)
    {
        // Prevent default error rendering
        return response()->noContent();
    }
}
