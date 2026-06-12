<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageCreditTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'quality',
        'credits_used',
        'approximate_cost_usd',
        'external_job_id',
    ];

    protected $casts = [
        'approximate_cost_usd' => 'float',
        'credits_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
