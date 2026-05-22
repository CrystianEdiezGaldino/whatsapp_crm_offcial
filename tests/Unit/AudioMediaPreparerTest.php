<?php

namespace Tests\Unit;

use App\Support\AudioMediaPreparer;
use Tests\TestCase;

class AudioMediaPreparerTest extends TestCase
{
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

    public function test_prepare_as_attachment_forces_mp3_filename(): void
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
