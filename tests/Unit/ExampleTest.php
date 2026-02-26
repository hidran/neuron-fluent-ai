<?php

namespace Tests\Unit;

use App\Models\ReadingRecording;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_disk_playback_url_uses_relative_storage_path(): void
    {
        $recording = new ReadingRecording([
            'storage_disk' => 'public',
            'audio_file_path' => '/reading-recordings/sample.webm',
        ]);

        $this->assertSame('/storage/reading-recordings/sample.webm', $recording->playbackUrl());
    }
}
