<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Converte áudio no servidor (Linux) antes do envio ao WhatsApp.
 * WebM/WAV → MP3 via ffmpeg no próprio host.
 */
class AudioConverter
{
    public function isAvailable(): bool
    {
        try {
            $result = Process::timeout(15)->run([$this->binary(), '-version']);

            return $result->successful() && str_contains($result->output() . $result->errorOutput(), 'ffmpeg');
        } catch (\Throwable) {
            return false;
        }
    }

    public function binary(): string
    {
        static $resolved = null;
        if ($resolved) {
            return $resolved;
        }

        $configured = config('services.whatsapp.ffmpeg_path');
        if (is_string($configured) && $configured !== '' && $configured !== 'ffmpeg') {
            if (is_executable($configured) || PHP_OS_FAMILY === 'Windows') {
                return $resolved = $configured;
            }
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            foreach (['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/bin/ffmpeg'] as $path) {
                if (is_executable($path)) {
                    return $resolved = $path;
                }
            }

            $which = trim((string) (shell_exec('command -v ffmpeg 2>/dev/null') ?: shell_exec('which ffmpeg 2>/dev/null')));
            if ($which !== '' && is_executable($which)) {
                return $resolved = $which;
            }
        }

        return $resolved = 'ffmpeg';
    }

    /**
     * @return array{path: string, mime: string, cleanup: string[]}|null
     */
    public function toOggVoice(string $sourcePath, string $originalName): ?array
    {
        return $this->convert($sourcePath, $originalName, 'ogg', 'audio/ogg', [
            '-c:a', 'libopus', '-ar', '48000', '-ac', '1',
        ]);
    }

    /**
     * @return array{path: string, mime: string, cleanup: string[]}|null
     */
    public function toMp3(string $sourcePath, string $originalName): ?array
    {
        return $this->convert($sourcePath, $originalName, 'mp3', 'audio/mpeg', [
            '-c:a', 'libmp3lame', '-b:a', '128k',
        ]);
    }

    /**
     * @param  array<int, string>  $encodeArgs
     * @return array{path: string, mime: string, cleanup: string[]}|null
     */
    private function convert(
        string $sourcePath,
        string $originalName,
        string $outExt,
        string $mime,
        array $encodeArgs
    ): ?array {
        if (!$this->isAvailable()) {
            return null;
        }

        $cleanup = [];
        $inputPath = $this->stageInput($sourcePath, $originalName);
        if (!$inputPath) {
            return null;
        }
        $cleanup[] = $inputPath;

        $tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $outPath = $tmpDir . 'wa_audio_' . Str::uuid() . '.' . $outExt;
        $cleanup[] = $outPath;

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)
            ?: pathinfo($sourcePath, PATHINFO_EXTENSION)
            ?: 'webm');

        $args = array_merge(
            [$this->binary(), '-y'],
            $this->inputFormatArgs($ext),
            ['-i', $inputPath],
            $encodeArgs,
            [$outPath]
        );

        $result = Process::timeout(120)->run($args);

        if (!$result->successful() || !is_file($outPath)) {
            Log::warning('AudioConverter: conversão falhou', [
                'exit' => $result->exitCode(),
                'stderr' => $result->errorOutput(),
                'stdout' => $result->output(),
                'source' => $sourcePath,
                'original_name' => $originalName,
                'binary' => $this->binary(),
                'out_ext' => $outExt,
            ]);
            $this->deleteFiles($cleanup);

            return null;
        }

        return [
            'path' => $outPath,
            'mime' => $mime,
            'cleanup' => $cleanup,
        ];
    }

    /** @return array<int, string> */
    private function inputFormatArgs(string $ext): array
    {
        return match ($ext) {
            'webm' => ['-f', 'webm'],
            'ogg', 'opus' => ['-f', 'ogg'],
            default => [],
        };
    }

    private function stageInput(string $source, string $originalName): ?string
    {
        $tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)
            ?: pathinfo($source, PATHINFO_EXTENSION)
            ?: 'webm');
        $staged = $tmpDir . 'wa_src_' . Str::uuid() . '.' . $ext;

        if (!is_readable($source) || !@copy($source, $staged)) {
            Log::warning('AudioConverter: falha ao copiar para /tmp', [
                'source' => $source,
                'dest' => $staged,
            ]);

            return null;
        }

        return $staged;
    }

    public function deleteFiles(array $paths): void
    {
        $tmp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach ($paths as $path) {
            if ($path && is_file($path) && str_starts_with($path, $tmp)) {
                @unlink($path);
            }
        }
    }
}
