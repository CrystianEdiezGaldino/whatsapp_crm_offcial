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

            // Create new Redis connection for pubsub
            $redis = Redis::connection()->client();

            try {
                // Send initial connection message
                echo "event: connected\n";
                echo "data: " . json_encode(['status' => 'connected', 'channel' => $channel]) . "\n\n";
                flush();

                Log::debug('[SSE] Subscribed to channel', ['channel' => $channel]);

                // Subscribe to channel
                $redis->subscribe([$channel], function ($redis, $type, $data) {
                    if ($type === 'message') {
                        // Send event
                        echo "data: {$data}\n\n";
                        flush();
                        Log::debug('[SSE] Sent message', ['size' => strlen($data)]);
                    }
                });
            } catch (\Exception $e) {
                Log::error('[SSE] Error in stream', [
                    'channel' => $channel,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Send error event
                echo "event: error\n";
                echo "data: " . json_encode(['error' => 'Connection failed']) . "\n\n";
                flush();
            }
        }, 200, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
