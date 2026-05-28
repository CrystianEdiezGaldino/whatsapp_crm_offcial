<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppNumber extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_numbers';

    protected $fillable = [
        'phone_number',
        'display_name',
        'access_token',
        'business_account_id',
        'phone_number_id',
        'is_active',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'metadata' => 'json',
    ];

    public static function active()
    {
        return self::where('is_active', true)->first();
    }

    public function setActive()
    {
        // Deactivate all others
        self::where('is_active', true)->update(['is_active' => false]);
        // Activate this one
        $this->update(['is_active' => true]);
    }
}
