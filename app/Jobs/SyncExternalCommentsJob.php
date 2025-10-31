<?php

namespace App\Jobs;

use App\Services\Comments\ExternalCommentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncExternalCommentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 минут
    public $maxExceptions = 3;

    public function handle(ExternalCommentService $externalCommentService): void
    {
        Log::info('Starting external comments synchronization');

        $importedCount = $externalCommentService->syncRecentComments();

        Log::info('External comments synchronization completed', [
            'imported_count' => $importedCount
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('External comments synchronization failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
