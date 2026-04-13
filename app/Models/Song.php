<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'file_path',
        'is_hot',
        'hot_rank',
        'is_active',
    ];

    public function getPlayableUrlAttribute(): ?string
    {
        return static::resolvePlayableUrl($this->file_path);
    }

    public static function resolvePlayableUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        // Only allow local files under public/ for playback.
        if (preg_match('~^https?://~i', $path)) {
            return null;
        }

        $relativePath = ltrim($path, '/');
        if ($relativePath === '') {
            return null;
        }

        if (!is_file(public_path($relativePath))) {
            return null;
        }

        return asset($relativePath);
    }
}
