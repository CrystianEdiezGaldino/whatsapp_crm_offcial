<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Services\DistributionService;
use Illuminate\Console\Command;

class ProcessDistributionQueue extends Command
{
    protected $signature = 'distribution:process-queue';
    protected $description = 'Process pending conversations in distribution queue (for automatic mode)';

    public function handle()
    {
        $this->info('Processing distribution queue...');

        // Get conversations without an active claim (regardless of status)
        $pendingConversations = Conversation::whereDoesntHave('activeClaim')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($pendingConversations->isEmpty()) {
            $this->info('No pending conversations to distribute.');
            return 0;
        }

        $this->info("Found {$pendingConversations->count()} pending conversations to distribute.");

        $distributed = 0;
        foreach ($pendingConversations as $conversation) {
            try {
                DistributionService::assign($conversation);
                $distributed++;
                $this->line("✓ Conversation #{$conversation->id} processed");
            } catch (\Exception $e) {
                $this->error("✗ Failed to process conversation #{$conversation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Distribution complete. {$distributed} conversations processed.");
        return 0;
    }
}
