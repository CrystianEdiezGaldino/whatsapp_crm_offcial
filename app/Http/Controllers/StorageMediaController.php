<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageMediaController extends Controller
{
    /** Fallback quando public/storage (symlink) não existe ou Apache não serve o arquivo. */
    public function show(string $path): BinaryFileResponse
    {
        $path = str_replace(['..', '\\'], ['', '/'], $path);

        if ($path === '' || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $full = Storage::disk('public')->path($path);
        $mime = mime_content_type($full) ?: 'application/octet-stream';

        return response()->file($full, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
