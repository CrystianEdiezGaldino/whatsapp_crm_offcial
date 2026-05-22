<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'assigned_to');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'assigned_to');
    }

    public function macros()
    {
        return $this->hasMany(Macro::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function claims()
    {
        return $this->hasMany(ConversationClaim::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
}
