<?php

namespace Tests\Unit;

use App\Helpers\ChatInboxHelper;
use App\Models\Message;
use Carbon\Carbon;
use Tests\TestCase;

class ChatInboxHelperTest extends TestCase
{
    public function test_dedupe_key_prefers_wa_message_id(): void
    {
        $message = new Message([
            'id' => 99,
            'wa_message_id' => 'wamid.TEST123',
        ]);

        $this->assertSame('wa:wamid.TEST123', ChatInboxHelper::dedupeKey($message));
    }

    public function test_to_client_array_includes_dedupe_key(): void
    {
        $message = new Message([
            'id' => 1,
            'wa_message_id' => 'wamid.ABC',
            'direction' => 'outbound',
            'type' => 'image',
            'content' => 'foto.jpg',
            'media_url' => 'media/x.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $message->created_at = Carbon::parse('2026-05-21 15:30:00');

        $payload = ChatInboxHelper::toClientArray($message);

        $this->assertSame('wa:wamid.ABC', $payload['dedupe_key']);
        $this->assertSame('image', $payload['type']);
        $this->assertSame('media/x.jpg', $payload['media_url']);
    }
}
