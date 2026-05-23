<?php

namespace App\Console\Commands;

use App\Services\MessageRetryService;
use Illuminate\Console\Command;

class ProcessMessageRetries extends Command
{
    protected $signature = 'messages:retry';
    protected $description = 'Process failed messages that need retry';

    public function handle()
    {
        $this->info('Processing message retries...');

        $processed = MessageRetryService::processRetries();
        $this->info("✅ Processed {$processed} messages");

        // Cleanup
        $cleaned = MessageRetryService::cleanup();
        $this->info("🗑️ Cleaned up {$cleaned} old records");
    }
}
