<?php

namespace Tests\Unit;

use App\Support\AudioMediaPreparer;
use Tests\TestCase;

class AudioMediaPreparerTest extends TestCase
{
    public function test_is_audio_file_detects_m4a_as_video_mp4(): void
    {
        $this->assertTrue(AudioMediaPreparer::isAudioFile('video/mp4', 'audio_1782331559288.m4a'));
        $this->assertSame('audio/mp4', AudioMediaPreparer::normalizeMime('video/mp4', 'audio_1782331559288.m4a'));
    }

    public function test_is_audio_file_detects_webm(): void
    {
        $this->assertTrue(AudioMediaPreparer::isAudioFile('audio/webm', 'gravacao.webm'));
    }

    public function test_prepare_mp3_without_conversion(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'mp3');
        file_put_contents($tmp, 'fake');

        $prepared = AudioMediaPreparer::prepare($tmp, 'audio/mpeg', 'audio.mp3', false);

        $this->assertSame('audio/mpeg', $prepared['mime']);
        $this->assertFalse($prepared['voice']);
        $this->assertSame($tmp, $prepared['path']);

        @unlink($tmp);
    }

    public function test_prepare_attachment_forces_mp3_filename(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'mp3');
        rename($tmp, $tmp . '.mp3');
        $tmp .= '.mp3';
        file_put_contents($tmp, 'fake');

        $prepared = AudioMediaPreparer::prepare($tmp, 'audio/mpeg', 'gravacao.webm', true, true);

        $this->assertFalse($prepared['voice']);
        $this->assertStringEndsWith('.mp3', $prepared['filename']);

        @unlink($tmp);
    }

    public function test_attachment_filename_adds_extension(): void
    {
        $this->assertSame('gravacao.mp3', AudioMediaPreparer::attachmentFilename('gravacao', 'audio/mpeg'));
    }
}
