<?php

namespace App\Http\Controllers;

use App\Models\Macro;
use App\Models\MacroFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MacroFileController extends Controller
{
    private const ALLOWED_MIME_TYPES = [
        'audio' => ['audio/mpeg', 'audio/mp4', 'audio/aac', 'audio/ogg', 'audio/webm'],
        'video' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    ];

    private const MAX_FILE_SIZE = 16 * 1024 * 1024; // 16MB

    public function store(Request $request, Macro $macro)
    {
        if ($macro->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Você não pode adicionar arquivos a este macro'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:16384', // 16MB in KB for validation
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileType = $this->getFileType($mimeType);

        if (!$fileType) {
            return response()->json(['success' => false, 'message' => 'Tipo de arquivo não suportado'], 422);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return response()->json(['success' => false, 'message' => 'Arquivo muito grande (máximo 16MB)'], 422);
        }

        $maxOrder = MacroFile::where('macro_id', $macro->id)->max('order_index') ?? -1;
        $path = $file->store("macros/{Auth::id()}/{$macro->id}", 'private');

        $macroFile = MacroFile::create([
            'macro_id' => $macro->id,
            'file_path' => $path,
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'file_type' => $fileType,
            'order_index' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Arquivo adicionado com sucesso',
            'file' => [
                'id' => $macroFile->id,
                'name' => $file->getClientOriginalName(),
                'type' => $fileType,
                'size' => $macroFile->getFileSizeFormatted(),
                'order_index' => $macroFile->order_index,
            ],
        ]);
    }

    public function destroy(Macro $macro, MacroFile $file)
    {
        if ($macro->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        if ($file->macro_id !== $macro->id) {
            return response()->json(['success' => false, 'message' => 'Arquivo não pertence a este macro'], 404);
        }

        Storage::disk('private')->delete($file->file_path);
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'Arquivo removido com sucesso',
        ]);
    }

    public function reorder(Request $request, Macro $macro, MacroFile $file)
    {
        if ($macro->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        $request->validate(['new_index' => 'required|integer|min:0']);

        if ($file->macro_id !== $macro->id) {
            return response()->json(['success' => false, 'message' => 'Arquivo não pertence a este macro'], 404);
        }

        $oldIndex = $file->order_index;
        $newIndex = $request->input('new_index');

        if ($oldIndex < $newIndex) {
            MacroFile::where('macro_id', $macro->id)
                ->whereBetween('order_index', [$oldIndex + 1, $newIndex])
                ->decrement('order_index');
        } else {
            MacroFile::where('macro_id', $macro->id)
                ->whereBetween('order_index', [$newIndex, $oldIndex - 1])
                ->increment('order_index');
        }

        $file->update(['order_index' => $newIndex]);

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada',
        ]);
    }

    public function preview(Macro $macro)
    {
        if ($macro->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        $files = $macro->files()
            ->orderBy('order_index')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => basename($f->file_path),
                'type' => $f->file_type,
                'size' => $f->getFileSizeFormatted(),
                'mime_type' => $f->mime_type,
                'icon' => $this->getFileIcon($f->file_type),
            ]);

        return response()->json([
            'success' => true,
            'content_type' => $macro->getContentType(),
            'text' => $macro->content,
            'files' => $files,
        ]);
    }

    private function getFileType(string $mimeType): ?string
    {
        foreach (self::ALLOWED_MIME_TYPES as $type => $mimes) {
            if (in_array($mimeType, $mimes)) {
                return $type;
            }
        }
        return null;
    }

    private function getFileIcon(string $fileType): string
    {
        return match($fileType) {
            'audio' => 'audio_file',
            'video' => 'videocam',
            'document' => 'description',
            'image' => 'image',
            default => 'attachment',
        };
    }
}
