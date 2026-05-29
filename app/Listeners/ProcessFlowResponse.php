<?php

namespace App\Listeners;

use App\Events\MessageReceived;
use App\Models\FlowExecution;
use App\Services\FlowService;

class ProcessFlowResponse
{
    public function __construct(private FlowService $flowService)
    {}

    public function handle(MessageReceived $event): void
    {
        $message = $event->message;
        $conversation = $message->conversation;

        // Check if there's an in-progress flow execution
        $execution = FlowExecution::where('conversation_id', $conversation->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (!$execution) {
            return;
        }

        // Check if message is a numeric choice
        if (is_numeric($message->content)) {
            $this->flowService->handleClientResponse(
                $conversation,
                (int) $message->content,
                $execution
            );
        }
    }
}
