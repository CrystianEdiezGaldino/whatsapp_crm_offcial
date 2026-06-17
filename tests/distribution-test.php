<?php
/**
 * Automated Distribution System Test
 *
 * Tests the queue processing and agent assignment logic
 * Run: php artisan tinker < tests/distribution-test.php
 */

use App\Models\Conversation;
use App\Models\Contact;
use App\Models\User;
use App\Models\AgentCapacity;
use App\Models\DistributionSetting;
use App\Services\DistributionService;

echo "\n=== Distribution System Test ===\n";

// Setup
echo "\n1. Verifying settings...\n";
$settings = DistributionSetting::current();
echo "   Mode: {$settings->mode}\n";
echo "   Overflow: {$settings->overflow_action}\n";

// Check agents
echo "\n2. Checking active agents...\n";
$agents = User::where('role', 'agent')->with('agentCapacity')->get();
echo "   Found " . $agents->count() . " agents\n";
foreach ($agents as $agent) {
    $capacity = $agent->agentCapacity;
    if ($capacity) {
        $activeCount = $capacity->activeConversationsCount();
        echo "   - {$agent->name}: {$activeCount}/{$capacity->max_conversations} conversations\n";
    }
}

// Create test conversations
echo "\n3. Creating test conversations...\n";
$testConversations = [];
for ($i = 0; $i < 3; $i++) {
    $contact = Contact::firstOrCreate(
        ['phone' => '55' . mt_rand(11, 99) . mt_rand(100000000, 999999999)],
        ['name' => 'Test Contact ' . ($i + 1)]
    );

    $conversation = Conversation::create([
        'contact_id' => $contact->id,
        'status' => 'new',
        'priority' => 'normal',
    ]);

    $testConversations[] = $conversation;
    echo "   ✓ Created conversation #{$conversation->id}\n";
}

// Process queue
echo "\n4. Processing distribution queue...\n";
$queued = DistributionService::getQueuedConversations();
echo "   Queued conversations: " . count($queued) . "\n";

$processed = 0;
foreach ($testConversations as $conv) {
    $before = $conv->status;
    DistributionService::assign($conv);
    $conv->refresh();
    $after = $conv->status;
    $agentName = $conv->claimer?->name ?? 'None';
    echo "   ✓ Conv #{$conv->id}: $before → $after (Assigned to: $agentName)\n";
    $processed++;
}

// Verify results
echo "\n5. Verification Results:\n";
foreach ($testConversations as $conv) {
    $conv->refresh();
    $isAssigned = $conv->claimed_by !== null;
    $status = $isAssigned ? '✓' : '✗';
    echo "   $status Conversation #{$conv->id}: status={$conv->status}, claimed_by={$conv->claimed_by}\n";
}

echo "\n6. Final Queue Status:\n";
$remainingQueue = DistributionService::getQueuedConversations();
echo "   Remaining in queue: " . count($remainingQueue) . "\n";

echo "\n=== Test Complete ===\n";
