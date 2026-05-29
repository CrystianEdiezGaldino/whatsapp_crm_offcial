<?php

namespace App\Services;

use App\Models\ConversationFlow;
use App\Models\FlowNode;

class FlowManagementService
{
    public function createFlow(array $data): ConversationFlow
    {
        $data['created_by'] = auth()->id();

        $flow = ConversationFlow::create($data);

        // Create nodes
        if (isset($data['nodes']) && is_array($data['nodes'])) {
            foreach ($data['nodes'] as $nodeData) {
                FlowNode::create([
                    'flow_id' => $flow->id,
                    ...$nodeData
                ]);
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
                FlowNode::create([
                    'flow_id' => $flow->id,
                    ...$nodeData
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
        $flow->delete();
    }
}
