<?php

namespace App\Services;

use App\Models\ConversationFlow;
use App\Models\FlowNode;

class FlowManagementService
{
    public function createFlow(array $data): ConversationFlow
    {
        \Log::debug('FlowManagementService::createFlow called', ['data' => $data]);

        $data['created_by'] = auth()->id();

        try {
            $flow = ConversationFlow::create($data);
            \Log::info('Flow created', ['flow_id' => $flow->id, 'name' => $flow->name]);
        } catch (\Exception $e) {
            \Log::error('Failed to create flow', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }

        // Create nodes
        if (isset($data['nodes']) && is_array($data['nodes'])) {
            \Log::debug('Creating flow nodes', ['count' => count($data['nodes'])]);

            foreach ($data['nodes'] as $index => $nodeData) {
                // Clean up empty target_sector_id
                if (isset($nodeData['target_sector_id']) && $nodeData['target_sector_id'] === '') {
                    $nodeData['target_sector_id'] = null;
                }

                try {
                    FlowNode::create([
                        'flow_id' => $flow->id,
                        'node_type' => $nodeData['node_type'] ?? 'menu',
                        'position' => $nodeData['position'] ?? 0,
                        'config' => $nodeData['config'] ?? [],
                        'target_sector_id' => $nodeData['target_sector_id'] ?? null,
                        'target_flow_id' => $nodeData['target_flow_id'] ?? null,
                    ]);
                    \Log::debug('Node created', ['node_index' => $index, 'flow_id' => $flow->id]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create node', ['error' => $e->getMessage(), 'node_index' => $index, 'nodeData' => $nodeData]);
                    throw $e;
                }
            }
        }

        return $flow;
    }

    public function updateFlow(ConversationFlow $flow, array $data): ConversationFlow
    {
        $flow->update($data);

        // Replace all nodes
        FlowNode::where('flow_id', $flow->id)->delete();

        if (isset($data['nodes']) && is_array($data['nodes'])) {
            foreach ($data['nodes'] as $nodeData) {
                // Clean up empty target_sector_id
                if (isset($nodeData['target_sector_id']) && $nodeData['target_sector_id'] === '') {
                    $nodeData['target_sector_id'] = null;
                }

                FlowNode::create([
                    'flow_id' => $flow->id,
                    'node_type' => $nodeData['node_type'] ?? 'menu',
                    'position' => $nodeData['position'] ?? 0,
                    'config' => $nodeData['config'] ?? [],
                    'target_sector_id' => $nodeData['target_sector_id'] ?? null,
                    'target_flow_id' => $nodeData['target_flow_id'] ?? null,
                ]);
            }
        }

        return $flow;
    }

    public function toggleFlow(ConversationFlow $flow): void
    {
        // If primary on_new_conversation: deactivate others of same type
        if ($flow->type === 'primary' && $flow->trigger_type === 'on_new_conversation') {
            ConversationFlow::where('id', '!=', $flow->id)
                ->where('type', 'primary')
                ->where('trigger_type', 'on_new_conversation')
                ->update(['is_active' => false]);
        }

        $flow->update(['is_active' => !$flow->is_active]);
    }

    public function deleteFlow(ConversationFlow $flow): void
    {
        $flow->executions()->delete();
        $flow->nodes()->delete();
        $flow->delete();
    }
}
