@php
    /** @var \App\Models\ReadingSession $record */
    $record = $getRecord();
    $recordings = $record->recordings;
@endphp

<div class="space-y-2 min-w-56">
    @if ($recordings->isEmpty())
        <p class="text-xs text-gray-500 dark:text-gray-400">No recordings</p>
    @else
        @foreach ($recordings as $recording)
            <div class="rounded border border-gray-200 dark:border-gray-700 p-2">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                    #{{ $recording->id }}{{ $recording->created_at ? ' · ' . $recording->created_at->format('Y-m-d H:i') : '' }}
                </p>

                <audio
                    controls
                    preload="none"
                    class="w-full max-w-56"
                    src="{{ $recording->playbackUrl() }}"
                ></audio>
            </div>
        @endforeach
    @endif
</div>
