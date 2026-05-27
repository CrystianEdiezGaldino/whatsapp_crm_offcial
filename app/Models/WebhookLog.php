<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'wa_message_id',
        'phone_number',
        'payload',
        'error_message',
        'ip_address',
        'processing_time_ms',
    ];
}
