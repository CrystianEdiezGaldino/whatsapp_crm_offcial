<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationFlow extends Model
{
    protected $fillable = [
        'name',
        'type',
        'trigger_type',
        'command_name',
        'is_active',
        'parent_flow_id',
        'config',
        'created_by'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean'
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(FlowNode::class, 'flow_id');
    }

    public function parentFlow(): BelongsTo
    {
        return $this->belongsTo(ConversationFlow::class, 'parent_flow_id');
    }

    public function subflows(): HasMany
    {
        return $this->hasMany(ConversationFlow::class, 'parent_flow_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(FlowExecution::class, 'flow_id');
    }
}
