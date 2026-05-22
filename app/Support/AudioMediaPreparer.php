<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AudioMediaPreparer
{
    /** MIME aceitos direto pela Cloud API (sem conversão). */
    private const NATIVE_MIMES = [
        'audio/aac' => 'audio/aac',
        'audio/mpeg' => 'audio/mpeg',
        'audio/mp3' => 'audio/mpeg',
        'audio/amr' => 'audio/amr',
        'audio/mp4' => 'audio/mp4',
        'audio/ogg' => 'audio/ogg',
    ];

    /**
     * Prepara arquivo para upload na Meta (método upload + media_id).
     *
     * @param  bool  $asAttachment  Envia como documento (anexo) — evita erro "mídia não existe" no áudio nativo.
     * @return array{path: string, mime: string, filename: string, voice: bool, cleanup: string[]}
     */
    public static function prepare(
        string $sourcePath,
        string $originalMime,
        string $originalName,
        bool $recorded = false,
        bool $asAttachment = false
    ): array {
        $mime = strtolower(trim($originalMime));
        $cleanup = [];

        if ($asAttachment) {
            $recorded = false;
        }

        $needsConvert = !isset(self::NATIVE_MIMES[$mime])
            || ($asAttachment && in_array($mime, ['audio/ogg', 'audio/webm'], true));

        if (!$needsConvert && isset(self::NATIVE_MIMES[$mime])) {
            $normalizedMime = self::NATIVE_MIMES[$mime];

            return [
                'path' => $sourcePath,
                'mime' => $normalizedMime,
                'filename' => $asAttachment
                    ? self::attachmentFilename($originalName, $normalizedMime)
                    : ($originalName ?: basename($sourcePath)),
                'voice' => false,
                'cleanup' => $cleanup,
            ];
        }

        $converted = self::convertWithFfmpeg($sourcePath, false);
        if ($converted) {
            $cleanup[] = $converted['path'];
            if ($asAttachment) {
                $converted['filename'] = self::attachmentFilename($originalName, 'audio/mpeg');
                $converted['voice'] = false;
            }

            return array_merge($converted, ['cleanup' => $cleanup]);
        }

        throw new \RuntimeException(
            'Áudio não suportado pelo WhatsApp (use MP3, M4A, OGG ou AAC). '
            . (self::ffmpegAvailable()
                ? 'Falha ao converter a gravação.'
                : 'Instale ffmpeg no servidor para converter gravações WebM.')
        );
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
        $bin = self::ffmpegBinary();
        $out = @shell_exec('"' . $bin . '" -version 2>&1');

        return is_string($out) && str_contains($out, 'ffmpeg');
    }

    public static function ffmpegBinary(): string
    {
        return config('services.whatsapp.ffmpeg_path', 'ffmpeg');
    }

    /** @return array{path: string, mime: string, filename: string, voice: bool}|null */
    private static function convertWithFfmpeg(string $source, bool $asVoice): ?array
    {
        if (!self::ffmpegAvailable()) {
            return null;
        }

        $bin = self::ffmpegBinary();
        $ext = $asVoice ? 'ogg' : 'mp3';
        $outPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wa_audio_' . Str::uuid() . '.' . $ext;

        if ($asVoice) {
            $cmd = sprintf(
                '"%s" -y -i %s -c:a libopus -ar 48000 -ac 1 %s 2>&1',
                $bin,
                escapeshellarg($source),
                escapeshellarg($outPath)
            );
            $mime = 'audio/ogg';
        } else {
            $cmd = sprintf(
                '"%s" -y -i %s -c:a libmp3lame -b:a 128k %s 2>&1',
                $bin,
                escapeshellarg($source),
                escapeshellarg($outPath)
            );
            $mime = 'audio/mpeg';
        }

        exec($cmd, $output, $code);

        if ($code !== 0 || !is_file($outPath)) {
            Log::warning('ffmpeg audio conversion failed', ['cmd' => $cmd, 'output' => $output, 'code' => $code]);

            return null;
        }

        return [
            'path' => $outPath,
            'mime' => $mime,
            'filename' => basename($outPath),
            'voice' => $asVoice,
        ];
    }

    public static function deleteCleanup(array $paths): void
    {
        $tmp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach ($paths as $path) {
            if (!$path || !is_file($path) || !str_starts_with($path, $tmp)) {
                continue;
            }
            @unlink($path);
        }
    }
}
