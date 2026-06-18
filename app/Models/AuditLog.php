<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['auditable_type', 'auditable_id', 'action', 'description', 'user_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];
    protected $casts = ['old_values' => 'json', 'new_values' => 'json', 'created_at' => 'datetime'];
    public $timestamps = true;

    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAuditableModel()
    {
        $model = 'App\\Models\\' . $this->auditable_type;
        if (class_exists($model)) {
            return $model::find($this->auditable_id);
        }
        return null;
    }

    public function getChangesFormatted(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue,
                ];
            }
        }
        return $changes;
    }
}