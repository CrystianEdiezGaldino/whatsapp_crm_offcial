<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowExecution extends Model
{
    protected $fillable = [
        'conversation_id',
        'flow_id',
        'node_id',
        'client_choice',
        'status',
        'result_sector_id',
        'result_subflow_id'
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ConversationFlow::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(FlowNode::class);
    }

    public function resultSector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'result_sector_id');
    }
}
