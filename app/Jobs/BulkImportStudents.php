<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Bytexr\QueueableBulkActions\Filament\Actions\ActionResponse;
use Bytexr\QueueableBulkActions\Jobs\BulkActionJob;

class BulkImportStudents extends BulkActionJob
{

    /**
     * Perform an action on each record.
     */
    protected function action($record, ?array $data): ActionResponse
    {
        // Your logic for importing students
        return ActionResponse::make()->success();
    }
}
