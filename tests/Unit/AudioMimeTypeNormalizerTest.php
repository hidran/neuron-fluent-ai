<?php

namespace Tests\Unit;

use App\Services\AudioMimeTypeNormalizer;
use PHPUnit\Framework\TestCase;

class AudioMimeTypeNormalizerTest extends TestCase
{
    public function test_it_normalizes_video_webm_to_audio_webm(): void
    {
        $normalizer = new AudioMimeTypeNormalizer;

        $this->assertSame('audio/webm', $normalizer->normalize('video/webm', 'recording.webm'));
    }

    public function test_it_falls_back_to_extension_when_mime_type_is_missing(): void
    {
        $normalizer = new AudioMimeTypeNormalizer;

        $this->assertSame('audio/ogg', $normalizer->normalize(null, 'recording.ogg'));
        $this->assertSame('audio/mpeg', $normalizer->normalize('', 'recording.mp3'));
    }

    public function test_it_normalizes_audio_x_wav_to_audio_wav(): void
    {
        $normalizer = new AudioMimeTypeNormalizer;

        $this->assertSame('audio/wav', $normalizer->normalize('audio/x-wav', 'recording.wav'));
    }

    public function test_it_keeps_supported_audio_mime_types_unchanged(): void
    {
        $normalizer = new AudioMimeTypeNormalizer;

        $this->assertSame('audio/webm', $normalizer->normalize('audio/webm; codecs=opus', 'recording.webm'));
    }
}
