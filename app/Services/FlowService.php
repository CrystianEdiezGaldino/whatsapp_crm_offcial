<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationFlow;
use App\Models\FlowExecution;
use App\Models\FlowNode;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class FlowService
{
    public function __construct(private WhatsAppService $whatsAppService)
    {}

    public function executeFlow(Conversation $conversation, ConversationFlow $flow): void
    {
        Log::info('[Flow] Starting flow execution', [
            'conversation_id' => $conversation->id,
            'flow_id' => $flow->id,
            'flow_name' => $flow->name,
        ]);

        $execution = FlowExecution::create([
            'conversation_id' => $conversation->id,
            'flow_id' => $flow->id,
            'status' => 'started',
        ]);

        $menuNodes = $flow->nodes()->orderBy('position')->get();

        $initialMessage = $flow->config['initial_message'] ?? null;
        if (!$initialMessage) {
            Log::warning('[Flow] No initial message configured', ['flow_id' => $flow->id]);
            $execution->update(['status' => 'completed']);
            return;
        }

        $text = $this->replaceVariables($initialMessage, $conversation);

        if ($menuNodes->isNotEmpty()) {
            $text .= "\n";
            foreach ($menuNodes as $node) {
                $nodeConfig = is_array($node->config) ? $node->config : [];
                $optionNumber = $nodeConfig['option_number'] ?? ($node->position + 1);
                $label = $nodeConfig['label'] ?? 'Opção ' . $optionNumber;
                $text .= "\n*{$optionNumber}* - {$label}";
            }
        }

        $this->sendMessage($conversation, $text);

        if ($menuNodes->isNotEmpty()) {
            $execution->update(['status' => 'in_progress']);
        } else {
            $execution->update(['status' => 'completed']);
        }
    }

    public function handleClientResponse(Conversation $conversation, int $clientChoice, FlowExecution $execution): void
    {
        $flow = $execution->flow;

        $chosenNode = $flow->nodes()
            ->get()
            ->first(function ($node) use ($clientChoice) {
                $config = is_array($node->config) ? $node->config : [];
                return ($config['option_number'] ?? null) == $clientChoice;
            });

        if (!$chosenNode) {
            $this->replayMenu($conversation, $flow);
            return;
        }

        $execution->update([
            'client_choice' => $clientChoice,
            'node_id' => $chosenNode->id,
        ]);

        $nodeConfig = is_array($chosenNode->config) ? $chosenNode->config : [];
        $label = $nodeConfig['label'] ?? 'Opção ' . $clientChoice;

        Log::info('[Flow] Client chose option', [
            'conversation_id' => $conversation->id,
            'flow_id' => $flow->id,
            'choice' => $clientChoice,
            'label' => $label,
            'target_sector_id' => $chosenNode->target_sector_id,
        ]);

        if ($chosenNode->target_sector_id) {
            $conversation->update(['sector_id' => $chosenNode->target_sector_id]);

            $finalMessage = $flow->config['final_message'] ?? null;
            if ($finalMessage) {
                $finalText = $this->replaceVariables($finalMessage, $conversation);
                $this->sendMessage($conversation, $finalText);
            }

            $execution->update([
                'status' => 'completed',
                'result_sector_id' => $chosenNode->target_sector_id,
            ]);

            Log::info('[Flow] Routing to sector', [
                'conversation_id' => $conversation->id,
                'sector_id' => $chosenNode->target_sector_id,
            ]);

            DistributionService::assign($conversation);
        } elseif ($chosenNode->target_flow_id) {
            $subflow = ConversationFlow::find($chosenNode->target_flow_id);
            if ($subflow) {
                $execution->update([
                    'status' => 'completed',
                    'result_subflow_id' => $subflow->id,
                ]);
                $this->executeFlow($conversation, $subflow);
            } else {
                $execution->update(['status' => 'completed']);
            }
        } else {
            $execution->update(['status' => 'completed']);
        }
    }

    private function replayMenu(Conversation $conversation, ConversationFlow $flow): void
    {
        $menuNodes = $flow->nodes()->orderBy('position')->get();

        $text = "Opção inválida. Por favor, escolha uma das opções abaixo:\n";
        foreach ($menuNodes as $node) {
            $nodeConfig = is_array($node->config) ? $node->config : [];
            $optionNumber = $nodeConfig['option_number'] ?? ($node->position + 1);
            $label = $nodeConfig['label'] ?? 'Opção ' . $optionNumber;
            $text .= "\n*{$optionNumber}* - {$label}";
        }

        $this->sendMessage($conversation, $text);
    }

    private function replaceVariables(string $text, Conversation $conversation): string
    {
        return app(VariableResolver::class)->replaceInText($text, $conversation);
    }

    private function sendMessage(Conversation $conversation, string $content): void
    {
        try {
            $response = $this->whatsAppService->sendText(
                $conversation->contact->phone,
                $content
            );

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'direction' => 'outbound',
                'content' => $content,
                'status' => $response ? 'sent' : 'failed',
            ]);

            if ($response && isset($response['messages'][0]['id'])) {
                $message->update(['external_id' => $response['messages'][0]['id']]);
            }

            Log::info('[Flow] Message sent', [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'status' => $message->status,
            ]);
        } catch (\Exception $e) {
            Log::error('[Flow] Failed to send message', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'direction' => 'outbound',
                'content' => $content,
                'status' => 'failed',
            ]);
        }
    }
}
