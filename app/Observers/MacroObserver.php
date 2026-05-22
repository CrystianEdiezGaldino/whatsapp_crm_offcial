<?php

namespace App\Observers;

use App\Models\Macro;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class MacroObserver
{
    public function created(Macro $macro): void
    {
        AuditLog::create([
            'auditable_type' => 'Macro',
            'auditable_id' => $macro->id,
            'action' => 'created',
            'description' => 'Macro criada: ' . $macro->name,
            'user_id' => Auth::id(),
            'new_values' => [
                'name' => $macro->name,
                'category' => $macro->category,
                'shortcut' => $macro->shortcut,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Macro $macro): void
    {
        $changes = $macro->getChanges();
        if (empty($changes) || count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        $original = $macro->getOriginal();
        $old = [];
        $new = [];

        foreach ($changes as $key => $value) {
            if ($key !== 'updated_at' && isset($original[$key])) {
                $old[$key] = $original[$key];
                $new[$key] = $value;
            }
        }

        if (empty($old)) {
            return;
        }

        AuditLog::create([
            'auditable_type' => 'Macro',
            'auditable_id' => $macro->id,
            'action' => 'updated',
            'description' => 'Macro atualizada: ' . $macro->name,
            'user_id' => Auth::id(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(Macro $macro): void
    {
        AuditLog::create([
            'auditable_type' => 'Macro',
            'auditable_id' => $macro->id,
            'action' => 'deleted',
            'description' => 'Macro deletada: ' . $macro->name,
            'user_id' => Auth::id(),
            'old_values' => [
                'name' => $macro->name,
                'category' => $macro->category,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
