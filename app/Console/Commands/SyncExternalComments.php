<?php

namespace App\Console\Commands;

use App\Jobs\SyncExternalCommentsJob;
use Illuminate\Console\Command;

/**
 * Команда для ручного запуска синхронизации
 */
class SyncExternalComments extends Command
{
    protected $signature = 'comments:sync-external';
    protected $description = 'Synchronize comments from external source';

    public function handle(): void
    {
        $this->info('Starting external comments synchronization...');

        SyncExternalCommentsJob::dispatch();

        $this->info('External comments synchronization job dispatched successfully.');
    }
}
