<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SSEController extends Controller
{
    public function subscribeToConversation(Request $request, $conversationId)
    {
        // Verify user has access to this conversation
        $conversation = Conversation::findOrFail($conversationId);

        Log::info('[SSE] Client connected to conversation', [
            'conversation_id' => $conversationId,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

        return $this->streamFromRedis("conversation.{$conversationId}");
    }

    public function subscribeToMessages(Request $request)
    {
        $conversationId = $request->query('conversation_id');
        $conversation = Conversation::findOrFail($conversationId);

        Log::info('[SSE] Client connected to messages channel', [
            'conversation_id' => $conversationId,
            'user_id' => auth()->id(),
        ]);

        return $this->streamFromRedis('messages.status');
    }

    public function subscribeToConversations(Request $request)
    {
        Log::info('[SSE] Client connected to conversations channel', [
            'user_id' => auth()->id(),
        ]);

        return $this->streamFromRedis('conversations.status');
    }

    private function streamFromRedis(string $channel)
    {
        return response()->stream(function () use ($channel) {
            // Set SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            // Send initial connection message
            echo "event: connected\n";
            echo "data: " . json_encode(['status' => 'connected', 'channel' => $channel]) . "\n\n";
            flush();

            Log::debug('[SSE] Subscribed to channel', ['channel' => $channel]);

            // Keep connection alive with heartbeat while polling fallback handles updates
            $startTime = time();
            while (time() - $startTime < 3600) { // Keep connection for 1 hour max
                // Send heartbeat every 15 seconds
                echo ": heartbeat\n\n";
                flush();
                sleep(15);
            }
        }, 200, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
