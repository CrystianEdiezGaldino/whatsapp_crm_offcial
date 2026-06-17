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

        // Get new conversations without an active claim
        $pendingConversations = Conversation::where('status', 'new')
            ->whereDoesntHave('activeClaim')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($pendingConversations->isEmpty()) {
            return 0;
        }

        $distributed = 0;
        foreach ($pendingConversations as $conversation) {
            try {
                DistributionService::assign($conversation);
                $distributed++;
            } catch (\Exception $e) {
                \Log::error("[Distribution] Failed to process conversation #{$conversation->id}: {$e->getMessage()}");
            }
        }

        if ($distributed > 0) {
            \Log::info("[Distribution Queue] Processed {$distributed} conversations");
        }
        return 0;
    }
}
