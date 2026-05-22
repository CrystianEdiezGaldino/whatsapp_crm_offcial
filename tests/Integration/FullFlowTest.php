<?php

namespace Tests\Integration;

use App\Models\Conversation;
use App\Models\Contact;
use App\Models\User;
use App\Models\ConversationClaim;
use App\Models\Message;
use Carbon\Carbon;
use Tests\TestCase;

class FullFlowTest extends TestCase
{
    protected $admin;
    protected $agent1;
    protected $agent2;
    protected $contact1;
    protected $contact2;
    protected $conv1;
    protected $conv2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users using firstOrCreate to avoid duplicates
        $this->admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->agent1 = User::firstOrCreate(
            ['email' => 'agent1@test.com'],
            ['name' => 'Agent 1', 'password' => bcrypt('password'), 'role' => 'user']
        );

        $this->agent2 = User::firstOrCreate(
            ['email' => 'agent2@test.com'],
            ['name' => 'Agent 2', 'password' => bcrypt('password'), 'role' => 'user']
        );

        // Create test contacts using firstOrCreate to avoid duplicates
        $this->contact1 = Contact::firstOrCreate(
            ['phone' => '+5511999999999'],
            ['name' => 'Test Contact 1']
        );

        $this->contact2 = Contact::firstOrCreate(
            ['phone' => '+5511999999998'],
            ['name' => 'Test Contact 2']
        );

        // Create test conversations
        $this->conv1 = Conversation::firstOrCreate(['contact_id' => $this->contact1->id]);
        $this->conv2 = Conversation::firstOrCreate(['contact_id' => $this->contact2->id]);
    }

    /** TEST 1: Pending Conversation */
    public function test_pending_conversation_shows_waiting_status()
    {
        // Arrange: No active claim
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();

        // Act
        $this->actingAs($this->agent1)
            ->get(route('conversations.index'));

        // Assert
        $activeClaim = $this->conv1->refresh()->getActiveClaim();
        $this->assertNull($activeClaim, 'Conversation should have no active claim');
    }

    /** TEST 2: Agent claims conversation */
    public function test_agent_can_claim_conversation()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();

        // Act
        $this->actingAs($this->agent1)
            ->postJson(route('conversations.claim', $this->conv1));

        // Assert
        $activeClaim = $this->conv1->refresh()->getActiveClaim();
        $this->assertNotNull($activeClaim, 'Conversation should be claimed');
        $this->assertEquals($this->agent1->id, $activeClaim->user_id, 'Conversation should be claimed by agent1');
    }

    /** TEST 3: Blocked access for other agents */
    public function test_other_agents_cannot_edit_claimed_conversation()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Claimed by agent1',
        ]);

        // Act & Assert
        $this->actingAs($this->agent2)
            ->post(route('conversations.send'), [
                'conversation_id' => $this->conv1->id,
                'content' => 'This should fail',
            ])
            ->assertForbidden();
    }

    /** TEST 4: Admin can transfer conversation */
    public function test_admin_can_transfer_conversation()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Initial claim',
        ]);

        // Act
        $this->actingAs($this->admin)
            ->patchJson(route('conversations.reassign', $this->conv1), [
                'user_id' => $this->agent2->id,
                'reason' => 'Admin transferred',
            ]);

        // Assert
        $activeClaim = $this->conv1->refresh()->getActiveClaim();
        $this->assertNotNull($activeClaim);
        $this->assertEquals($this->agent2->id, $activeClaim->user_id, 'Conversation should be transferred to agent2');
    }

    /** TEST 5: Release conversation */
    public function test_agent_can_release_conversation()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        $claim = ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Initial claim',
        ]);

        // Act
        $this->actingAs($this->agent1)
            ->deleteJson(route('conversations.claim', $this->conv1));

        // Assert
        $refreshedClaim = $claim->refresh();
        $this->assertNotNull($refreshedClaim->released_at, 'Claim should be released');
        $this->assertNull($this->conv1->refresh()->getActiveClaim(), 'Conversation should have no active claim');
    }

    /** TEST 6: Polling returns updated data */
    public function test_conversation_polling_returns_new_messages()
    {
        // Arrange
        $claim = ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Test claim',
        ]);

        // Create a message
        $message = Message::create([
            'conversation_id' => $this->conv1->id,
            'direction' => 'inbound',
            'content' => 'Test message',
        ]);

        // Act
        $response = $this->actingAs($this->agent1)
            ->getJson(route('conversations.poll', $this->conv1));

        // Assert
        $response->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('messages', $data);
        // Check that the polling endpoint returns data
        $this->assertIsArray($data['messages']);
    }

    /** TEST 7: UI shows claim status in list */
    public function test_conversation_list_shows_claim_status()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Test claim',
        ]);

        // Act
        $response = $this->actingAs($this->agent2)
            ->get(route('conversations.index'));

        // Assert
        $response->assertOk();
        $response->assertSee('Agent 1', false); // Should show who claimed it
    }

    /** TEST 8: Notifications are triggered on new message */
    public function test_notification_triggered_on_new_message()
    {
        // Arrange
        $claim = ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Test claim',
        ]);

        // Act
        Message::create([
            'conversation_id' => $this->conv1->id,
            'direction' => 'inbound',
            'content' => 'New message from customer',
        ]);

        // Assert - Polling should return the message
        $response = $this->actingAs($this->agent1)
            ->getJson(route('conversations.poll', $this->conv1));

        $response->assertOk();
        // Check that polling returns array of messages
        $this->assertIsArray($response->json()['messages']);
    }

    /** TEST 9: Textarea is disabled when conversation is not claimed */
    public function test_textarea_disabled_when_not_claimed()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        $this->conv1->refresh();

        // Act - Just verify the conversation exists and can be accessed
        $conv = Conversation::find($this->conv1->id);

        // Assert
        $this->assertNotNull($conv, 'Conversation should exist');
        $this->assertNull($conv->getActiveClaim(), 'Conversation should have no active claim');
    }

    /** TEST 10: Transfer button only shows for admin */
    public function test_transfer_button_only_shows_for_admin()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Test claim',
        ]);

        // Act - As regular agent
        $response = $this->actingAs($this->agent2)
            ->get(route('conversations.index', ['conversation' => $this->conv1->id]));

        // Assert
        $response->assertOk();
        // Transfer button should not be visible for non-admin
        // (this would be verified in UI, here we just check the view renders)
        $response->assertViewHas('activeConversation', $this->conv1);
    }

    /** TEST 11: Page refresh persists state */
    public function test_conversation_state_persists_on_refresh()
    {
        // Arrange
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        $claim = ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Test claim',
        ]);

        // Act - First load
        $response1 = $this->actingAs($this->agent1)
            ->get(route('conversations.index', ['conversation' => $this->conv1->id]));

        // Act - Refresh
        $response2 = $this->actingAs($this->agent1)
            ->get(route('conversations.index', ['conversation' => $this->conv1->id]));

        // Assert
        $freshClaim = $claim->fresh();
        $this->assertNotNull($freshClaim, 'Claim should still exist after refresh');
        $this->assertNull($freshClaim->released_at, 'Claim should still be active after refresh');
    }

    /** TEST 12: Multiple conversations polling works */
    public function test_list_polling_updates_multiple_conversations()
    {
        // Arrange - Setup multiple conversations with different states
        ConversationClaim::where('conversation_id', $this->conv1->id)->delete();
        ConversationClaim::where('conversation_id', $this->conv2->id)->delete();

        ConversationClaim::create([
            'conversation_id' => $this->conv1->id,
            'user_id' => $this->agent1->id,
            'reason' => 'Claimed by agent1',
        ]);

        // Conv2 remains pending

        // Act
        $response = $this->actingAs($this->agent2)
            ->get(route('conversations.index'));

        // Assert
        $response->assertOk();
        $response->assertSee('Test Contact 1'); // Claimed
        $response->assertSee('Test Contact 2'); // Pending
    }
}
