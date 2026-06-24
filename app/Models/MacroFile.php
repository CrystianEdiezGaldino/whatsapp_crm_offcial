<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroFile extends Model
{
    protected $fillable = ['macro_id', 'file_path', 'mime_type', 'file_size', 'file_type', 'order_index'];
    protected $casts = ['file_size' => 'integer', 'order_index' => 'integer'];

    public function macro(): BelongsTo
    {
        return $this->belongsTo(Macro::class);
    }

    public function getDownloadUrl(): string
    {
        return url('storage/' . $this->file_path);
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        if (!$bytes || $bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
