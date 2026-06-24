<?php

namespace App\Support;

use App\Services\AudioConverter;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AudioMediaPreparer
{
    private const AUDIO_EXTENSIONS = ['m4a', 'mp3', 'aac', 'amr', 'ogg', 'opus', 'webm', 'wav'];

    /** MIME aceitos direto pela Cloud API (sem conversão). */
    private const NATIVE_MIMES = [
        'audio/aac' => 'audio/aac',
        'audio/mpeg' => 'audio/mpeg',
        'audio/mp3' => 'audio/mpeg',
        'audio/amr' => 'audio/amr',
        'audio/mp4' => 'audio/mp4',
        'audio/ogg' => 'audio/ogg',
    ];

    public static function isAudioFile(string $mime, string $filename): bool
    {
        $mime = strtolower(trim($mime));
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (str_starts_with($mime, 'audio/')) {
            return true;
        }

        if (in_array($ext, self::AUDIO_EXTENSIONS, true)) {
            return true;
        }

        return $mime === 'video/mp4' && $ext === 'm4a';
    }

    public static function normalizeMime(string $mime, string $filename): string
    {
        $mime = strtolower(trim($mime));

        if (str_starts_with($mime, 'audio/')) {
            return self::NATIVE_MIMES[$mime] ?? $mime;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'm4a' => 'audio/mp4',
            'mp3' => 'audio/mpeg',
            'aac' => 'audio/aac',
            'amr' => 'audio/amr',
            'ogg', 'opus' => 'audio/ogg',
            'webm' => 'audio/webm',
            'wav' => 'audio/wav',
            default => $mime,
        };
    }

    /**
     * Prepara áudio para envio. Com $asAttachment=true (padrão outbound API-only),
     * normaliza para MP3 e envia como documento — evita "mídia não existe" no celular.
     *
     * @return array{path: string, mime: string, filename: string, voice: bool, cleanup: string[]}
     */
    public static function prepare(
        string $sourcePath,
        string $originalMime,
        string $originalName,
        bool $recorded = false,
        bool $asAttachment = true
    ): array {
        $mime = strtolower(trim($originalMime));
        $converter = app(AudioConverter::class);

        if ($asAttachment) {
            if (in_array($mime, ['audio/mpeg', 'audio/mp3'], true)) {
                return [
                    'path' => $sourcePath,
                    'mime' => 'audio/mpeg',
                    'filename' => self::attachmentFilename($originalName, 'audio/mpeg'),
                    'voice' => false,
                    'cleanup' => [],
                ];
            }

            $converted = $converter->toMp3($sourcePath, $originalName);
            if (!$converted) {
                throw new RuntimeException(
                    'Áudio não suportado pelo WhatsApp (use MP3, M4A, OGG ou AAC). '
                    . ($converter->isAvailable()
                        ? 'Falha ao converter no servidor.'
                        : 'Instale ffmpeg no servidor Linux: apt install ffmpeg')
                );
            }

            return [
                'path' => $converted['path'],
                'mime' => 'audio/mpeg',
                'filename' => self::attachmentFilename($originalName, 'audio/mpeg'),
                'voice' => false,
                'cleanup' => $converted['cleanup'],
            ];
        }

        if ($recorded) {
            $converted = ($mime === 'audio/ogg')
                ? ['path' => $sourcePath, 'mime' => 'audio/ogg', 'cleanup' => []]
                : $converter->toOggVoice($sourcePath, $originalName);

            if (!$converted) {
                throw new RuntimeException(
                    'Falha ao preparar mensagem de voz. '
                    . ($converter->isAvailable()
                        ? 'Conversão OGG falhou.'
                        : 'Instale ffmpeg no servidor Linux: apt install ffmpeg')
                );
            }

            return [
                'path' => $converted['path'],
                'mime' => $converted['mime'],
                'filename' => pathinfo($originalName, PATHINFO_FILENAME) . '.ogg',
                'voice' => true,
                'cleanup' => $converted['cleanup'],
            ];
        }

        if (isset(self::NATIVE_MIMES[$mime])) {
            $normalizedMime = self::NATIVE_MIMES[$mime];

            return [
                'path' => $sourcePath,
                'mime' => $normalizedMime,
                'filename' => $originalName ?: basename($sourcePath),
                'voice' => false,
                'cleanup' => [],
            ];
        }

        $converted = $converter->toMp3($sourcePath, $originalName);

        if (!$converted) {
            throw new RuntimeException(
                'Áudio não suportado pelo WhatsApp (use MP3, M4A, OGG ou AAC). '
                . ($converter->isAvailable()
                    ? 'Falha ao converter no servidor.'
                    : 'Instale ffmpeg no servidor Linux: apt install ffmpeg')
            );
        }

        return [
            'path' => $converted['path'],
            'mime' => $converted['mime'],
            'filename' => pathinfo($originalName, PATHINFO_FILENAME) . '.mp3',
            'voice' => false,
            'cleanup' => $converted['cleanup'],
        ];
    }

    public static function attachmentFilename(string $originalName, string $mime): string
    {
        $ext = match ($mime) {
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/aac' => 'aac',
            'audio/amr' => 'amr',
            default => 'mp3',
        };

        $base = pathinfo($originalName, PATHINFO_FILENAME) ?: 'audio';

        if (str_ends_with(strtolower($originalName), '.' . $ext)) {
            return $originalName;
        }

        return $base . '.' . $ext;
    }

    public static function ffmpegAvailable(): bool
    {
        return app(AudioConverter::class)->isAvailable();
    }

    public static function ffmpegBinary(): string
    {
        return app(AudioConverter::class)->binary();
    }

    public static function persistToPublicDisk(string $sourcePath, string $extension = 'mp3'): string
    {
        $extension = strtolower(ltrim($extension, '.')) ?: 'mp3';
        $stored = Storage::disk('public')->putFileAs(
            'media',
            new File($sourcePath),
            Str::uuid() . '.' . $extension
        );

        if (!$stored || !Storage::disk('public')->exists($stored)) {
            throw new RuntimeException('Falha ao salvar áudio em storage/app/public/media.');
        }

        return $stored;
    }

    public static function deleteCleanup(array $paths): void
    {
        app(AudioConverter::class)->deleteFiles($paths);
    }
}
