<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Macro extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'content', 'shortcut', 'category',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(MacroFile::class)->orderBy('order_index');
    }

    public function hasFiles(): bool
    {
        try {
            return $this->files()->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getContentType(): string
    {
        return $this->hasFiles() ? 'files' : 'text';
    }
}
