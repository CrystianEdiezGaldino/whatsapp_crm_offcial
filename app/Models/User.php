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
        'sector_id', 'is_active', 'image_credits', 'notes',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'image_credits' => 'integer',
    ];

    public function imageCreditTransactions()
    {
        return $this->hasMany(ImageCreditTransaction::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

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

    public function agentCapacity()
    {
        return $this->hasOne(AgentCapacity::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function getSectorName(): string
    {
        return $this->sector?->name ?? 'Sem setor';
    }

    public function getStatusLabel(): string
    {
        if (!$this->isActive()) {
            return '❌ Inativo';
        }
        return $this->status === 'online' ? '🟢 Online' : '⚪ Offline';
    }

    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin' => '👤 Administrador',
            'supervisor' => '👨‍💼 Supervisor',
            'agent' => '👨‍💻 Atendente',
            default => $this->role,
        };
    }
}
