<?php

namespace Tests\Unit;

use App\Models\Conversation;
use App\Models\ConversationClaim;
use App\Models\Message;
use Tests\TestCase;

class ConversationPendingQueueTest extends TestCase
{
    public function test_new_without_claim_is_pending(): void
    {
        $conv = new Conversation(['status' => 'new', 'claimed_by' => null]);
        $conv->setRelation('activeClaim', null);
        $conv->setRelation('lastMessage', new Message(['content' => 'oi']));

        $this->assertTrue($conv->isPendingInQueue());
    }

    public function test_in_attendance_without_claim_is_not_pending(): void
    {
        $conv = new Conversation(['status' => 'in_attendance', 'claimed_by' => null]);

        $this->assertFalse($conv->isPendingInQueue());
    }

    public function test_new_with_claimed_by_is_not_pending(): void
    {
        $conv = new Conversation(['status' => 'new', 'claimed_by' => 5]);

        $this->assertFalse($conv->isPendingInQueue());
    }

    public function test_active_claim_makes_not_pending(): void
    {
        $conv = new Conversation(['status' => 'new', 'claimed_by' => null]);
        $claim = new ConversationClaim(['user_id' => 1, 'released_at' => null]);
        $conv->setRelation('activeClaim', $claim);

        $this->assertFalse($conv->isPendingInQueue());
    }

    public function test_empty_conversation_is_not_pending(): void
    {
        $conv = new Conversation(['status' => 'new', 'claimed_by' => null]);
        $conv->setRelation('activeClaim', null);
        $conv->setRelation('lastMessage', null);

        $this->assertFalse($conv->isPendingInQueue());
    }

    public function test_new_with_message_is_pending(): void
    {
        $conv = new Conversation(['status' => 'new', 'claimed_by' => null]);
        $conv->setRelation('activeClaim', null);
        $conv->setRelation('lastMessage', new \App\Models\Message(['content' => 'oi']));

        $this->assertTrue($conv->isPendingInQueue());
    }
}
