<?php

namespace Tests\Unit;

use App\Support\WhatsAppApiError;
use PHPUnit\Framework\TestCase;

class WhatsAppApiErrorTest extends TestCase
{
    public function test_message_for_allowed_list_error(): void
    {
        $msg = WhatsAppApiError::userMessage(['code' => 131030, 'message' => 'Recipient phone number not in allowed list']);

        $this->assertStringContainsString('lista de teste', $msg);
        $this->assertStringContainsString('554197796908', $msg);
    }
}
