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
        $execution = FlowExecution::create([
            'conversation_id' => $conversation->id,
            'flow_id' => $flow->id,
            'status' => 'started',
            'current_step' => 1
        ]);

        $this->processStep($conversation, $flow, $execution, 1);
    }

    private function processStep(Conversation $conversation, ConversationFlow $flow, FlowExecution $execution, int $stepNumber): void
    {
        $step = $flow->nodes()
            ->where('position', $stepNumber)
            ->first();

        if (!$step) {
            Log::warning('[Flow] Step not found', ['flow_id' => $flow->id, 'step' => $stepNumber]);
            $execution->update(['status' => 'completed']);
            return;
        }

        $execution->update(['current_step' => $stepNumber]);

        match ($step->node_type) {
            'message' => $this->handleMessage($conversation, $flow, $execution, $step, $stepNumber),
            'menu' => $this->handleMenu($conversation, $flow, $execution, $step),
            'queue' => $this->handleQueue($conversation, $flow, $execution, $step),
            default => Log::error('[Flow] Unknown node type', ['type' => $step->node_type])
        };
    }

    private function handleMessage(Conversation $conversation, ConversationFlow $flow, FlowExecution $execution, FlowNode $step, int $currentStep): void
    {
        $text = $step->config['text'] ?? '';
        $text = $this->replaceVariables($text, $conversation);

        $this->sendMessage($conversation, $text);

        // Move to next step
        $nextStep = $currentStep + 1;
        $nextStepExists = $flow->nodes()->where('position', $nextStep)->exists();

        if ($nextStepExists) {
            $this->processStep($conversation, $flow, $execution, $nextStep);
        } else {
            $execution->update(['status' => 'completed']);
        }
    }

    private function handleMenu(Conversation $conversation, ConversationFlow $flow, FlowExecution $execution, FlowNode $step): void
    {
        $text = $step->config['text'] ?? 'Escolha uma opção:';
        $options = $step->config['options'] ?? [];

        // Build menu text
        $menuText = $text . "\n\n";
        foreach ($options as $option) {
            $menuText .= "{$option['number']}. {$option['label']}\n";
        }

        $this->sendMessage($conversation, $menuText);

        // Wait for response
        $execution->update(['status' => 'in_progress']);
    }

    private function handleQueue(Conversation $conversation, ConversationFlow $flow, FlowExecution $execution, FlowNode $step): void
    {
        // Send confirmation message
        $text = $step->config['text'] ?? 'Você será atendido em breve. Obrigado!';
        $text = $this->replaceVariables($text, $conversation);
        $this->sendMessage($conversation, $text);

        // Assign sector and complete flow
        $sectorId = $step->target_sector_id;
        if ($sectorId) {
            $conversation->update(['sector_id' => $sectorId]);

            // Distribute to agent
            Log::info('[Flow] Assigning to queue', [
                'conversation_id' => $conversation->id,
                'sector_id' => $sectorId
            ]);

            DistributionService::assign($conversation);
        }

        $execution->update(['status' => 'completed', 'result_sector_id' => $sectorId]);
    }

    public function handleClientResponse(Conversation $conversation, int $clientChoice, FlowExecution $execution): void
    {
        $currentStep = $execution->current_step;
        $flow = $execution->flow;

        $step = $flow->nodes()
            ->where('position', $currentStep)
            ->where('node_type', 'menu')
            ->first();

        if (!$step) {
            return;
        }

        // Find which option was chosen
        $options = $step->config['options'] ?? [];
        $chosenOption = collect($options)->firstWhere('number', $clientChoice);

        if (!$chosenOption) {
            // Invalid option: replay menu
            $this->replayMenu($conversation, $step);
            return;
        }

        // Register choice
        $execution->update(['client_choice' => $clientChoice]);

        // Find next step (option_number maps to step number)
        // For simplicity: option 1 → step position after menu
        // We need to figure out routing logic

        // Option-based routing: each option goes to a different step
        $optionIndex = array_search($clientChoice, array_column($options, 'number'));

        // Logic: after menu at position X, next steps are X+1, X+2, X+3 for options 1, 2, 3
        $nextStep = $step->position + 1 + $optionIndex;

        $this->processStep($conversation, $flow, $execution, $nextStep);
    }

    private function replayMenu(Conversation $conversation, FlowNode $step): void
    {
        $text = $step->config['text'] ?? 'Opção inválida. Tente novamente:';
        $options = $step->config['options'] ?? [];

        $menuText = $text . "\n\n";
        foreach ($options as $option) {
            $menuText .= "{$option['number']}. {$option['label']}\n";
        }

        $this->sendMessage($conversation, $menuText);
    }

    private function replaceVariables(string $text, Conversation $conversation): string
    {
        $variables = app(VariableResolver::class)->resolve($conversation);

        foreach ($variables as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }

        return $text;
    }

    private function sendMessage(Conversation $conversation, string $content): void
    {
        try {
            // Send via WhatsApp
            $response = $this->whatsAppService->sendText(
                $conversation->contact->phone,
                $content
            );

            // Create message record
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'direction' => 'outbound',
                'content' => $content,
                'status' => $response ? 'sent' : 'failed'
            ]);

            if ($response && isset($response['messages'][0]['id'])) {
                $message->update(['external_id' => $response['messages'][0]['id']]);
            }

            Log::info('[Flow] Message sent', [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'status' => $message->status
            ]);
        } catch (\Exception $e) {
            Log::error('[Flow] Failed to send message', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage()
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'direction' => 'outbound',
                'content' => $content,
                'status' => 'failed'
            ]);
        }
    }
}
