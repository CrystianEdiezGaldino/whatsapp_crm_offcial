<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationFlow;
use App\Models\FlowExecution;
use App\Models\FlowNode;
use App\Models\Message;

class FlowService
{
    public function executeFlow(Conversation $conversation, ConversationFlow $flow): void
    {
        $execution = FlowExecution::create([
            'conversation_id' => $conversation->id,
            'flow_id' => $flow->id,
            'status' => 'started'
        ]);

        $this->processFlowNodes($conversation, $flow, $execution);
    }

    private function processFlowNodes(Conversation $conversation, ConversationFlow $flow, FlowExecution $execution): void
    {
        // Send initial message
        $this->sendMessage($conversation, $flow->config['initial_message'] ?? 'Olá!');

        // Get menu nodes
        $menuNodes = $flow->nodes()
            ->where('node_type', 'menu')
            ->orderBy('position')
            ->get();

        if ($menuNodes->isEmpty()) {
            // No menu: send final message and complete
            $this->sendMessage($conversation, $flow->config['final_message'] ?? 'Obrigado!');
            $execution->update(['status' => 'completed']);
            return;
        }

        // Update execution to in_progress (waiting for client response)
        $execution->update(['status' => 'in_progress']);
    }

    public function handleClientResponse(Conversation $conversation, int $clientChoice, FlowExecution $execution): void
    {
        $menuNode = $execution->flow->nodes()
            ->where('node_type', 'menu')
            ->whereJsonContains('config->option_number', $clientChoice)
            ->first();

        if (!$menuNode) {
            // Invalid option: replay menu
            $this->replayMenu($conversation, $execution->flow);
            return;
        }

        // Register choice
        $execution->update([
            'node_id' => $menuNode->id,
            'client_choice' => $clientChoice
        ]);

        // If targets a subflow: execute it
        if ($menuNode->target_flow_id) {
            $subflow = ConversationFlow::find($menuNode->target_flow_id);
            if ($subflow) {
                $this->executeFlow($conversation, $subflow);
                return;
            }
        }

        // If targets a sector: complete flow
        $this->sendMessage($conversation, $execution->flow->config['final_message'] ?? 'Obrigado!');

        if ($menuNode->target_sector_id) {
            $this->completeFlow($conversation, $execution, $menuNode->target_sector_id);
        }
    }

    private function completeFlow(Conversation $conversation, FlowExecution $execution, ?int $sectorId): void
    {
        $execution->update([
            'status' => 'completed',
            'result_sector_id' => $sectorId
        ]);

        if ($sectorId) {
            $conversation->update(['sector_id' => $sectorId]);
        }
    }

    private function replayMenu(Conversation $conversation, ConversationFlow $flow): void
    {
        $menuText = "Por favor, escolha uma opção válida:\n";
        $menuNodes = $flow->nodes()
            ->where('node_type', 'menu')
            ->orderBy('position')
            ->get();

        foreach ($menuNodes as $node) {
            $number = $node->config['option_number'] ?? '?';
            $label = $node->config['label'] ?? 'Opção';
            $menuText .= "$number. $label\n";
        }

        $this->sendMessage($conversation, $menuText);
    }

    private function sendMessage(Conversation $conversation, string $content): void
    {
        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'content' => $content,
            'status' => 'pending'
        ]);
    }
}
