<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowNode extends Model
{
    protected $fillable = [
        'flow_id',
        'node_type',
        'position',
        'config',
        'target_sector_id',
        'target_flow_id'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ConversationFlow::class, 'flow_id');
    }

    public function targetSector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'target_sector_id');
    }

    public function targetFlow(): BelongsTo
    {
        return $this->belongsTo(ConversationFlow::class, 'target_flow_id');
    }
}
