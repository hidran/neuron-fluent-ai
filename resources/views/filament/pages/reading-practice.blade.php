<x-filament-panels::page>
    @php
        $savedRecordingCount = count($savedRecordings);
        $selectedLanguage = data_get($data, 'selectedLanguage');
        $languageLabels = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
        ];
        $selectedLanguageLabel = $languageLabels[$selectedLanguage] ?? (filled($selectedLanguage) ? strtoupper((string) $selectedLanguage) : null);
        $feedbackText = trim((string) data_get($feedback, 'feedback', ''));
        $feedbackParagraphs = $feedbackText === ''
            ? []
            : array_values(array_filter(array_map('trim', preg_split('/\R{2,}|\R/', $feedbackText) ?: [])));
    @endphp

    <div class="space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Reading Practice</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Choose a topic and language, generate a reading, then record and analyze your pronunciation.
                    </p>
                </div>

                @if ($savedRecordingCount)
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                        {{ $savedRecordingCount }} saved {{ $savedRecordingCount === 1 ? 'recording' : 'recordings' }}
                    </span>
                @endif
            </div>

            <form wire:submit="generateText" class="space-y-6">
                <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40 sm:p-5">
                    {{ $this->form }}
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-filament::button type="submit" wire:loading.attr="disabled">
                        Generate Reading Text
                    </x-filament::button>

                    @if ($generatedText)
                        <x-filament::button
                            color="success"
                            id="analyzeRecording"
                            type="button"
                        >
                            Analyze Recording
                        </x-filament::button>
                    @endif
                </div>
            </form>
        </section>

        @if ($generatedText)
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
                <div class="space-y-6">
                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-8">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Reading Text</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    Read this passage aloud, then record your attempt.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($selectedLanguageLabel)
                                    <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700 dark:border-sky-800 dark:bg-sky-900/30 dark:text-sky-300">
                                        {{ $selectedLanguageLabel }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $savedRecordingCount }} attempt{{ $savedRecordingCount === 1 ? '' : 's' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50/70 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6">
                            <p class="whitespace-pre-line text-base leading-8 text-gray-800 dark:text-gray-100">
                                {{ $generatedText }}
                            </p>
                        </div>

                        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900 sm:p-6">
                            <div class="mb-4 flex flex-col gap-1">
                                <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                                    Recorder
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Start, stop, and preview your attempt before sending it for analysis.
                                </p>
                            </div>

                            <div id="audioRecorder" class="space-y-4">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <x-filament::button
                                        color="danger"
                                        id="startRecording"
                                        type="button"
                                        class="justify-center"
                                    >
                                        <x-filament::icon icon="heroicon-o-microphone" class="mr-2 h-5 w-5" />
                                        Start Recording
                                    </x-filament::button>

                                    <x-filament::button
                                        color="gray"
                                        id="stopRecording"
                                        type="button"
                                        class="justify-center"
                                    >
                                        <x-filament::icon icon="heroicon-o-stop" class="mr-2 h-5 w-5" />
                                        Stop Recording
                                    </x-filament::button>

                                    <x-filament::button
                                        color="info"
                                        id="playRecording"
                                        type="button"
                                        class="justify-center"
                                    >
                                        <x-filament::icon icon="heroicon-o-play" class="mr-2 h-5 w-5" />
                                        Play Recording
                                    </x-filament::button>
                                </div>

                                <audio id="audioPlayback" controls class="w-full rounded-lg" style="display: none;"></audio>

                                <p id="recordingStatus" class="min-h-5 text-sm text-gray-600 dark:text-gray-300"></p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    @if ($feedback)
                        <section class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/20 sm:p-8">
                            <div class="flex flex-col gap-2">
                                <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-200">
                                    Pronunciation Assessment
                                </h3>
                                <p class="text-sm text-emerald-800/90 dark:text-emerald-200/80">
                                    Latest AI coaching result for this reading. Detailed feedback is shown only here to avoid duplication.
                                </p>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="rounded-xl border border-white/70 bg-white/90 p-4 dark:border-gray-700 dark:bg-gray-900/80">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Pronunciation
                                    </div>
                                    <div class="mt-2 text-3xl font-semibold text-blue-600 dark:text-blue-400">
                                        {{ $feedback['pronunciation'] }}/100
                                    </div>
                                    <div class="mt-3 h-2 rounded-full bg-blue-100 dark:bg-blue-950/60">
                                        <div
                                            class="h-2 rounded-full bg-blue-500"
                                            style="width: {{ max(0, min(100, (int) $feedback['pronunciation'])) }}%;"
                                        ></div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-white/70 bg-white/90 p-4 dark:border-gray-700 dark:bg-gray-900/80">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Intonation
                                    </div>
                                    <div class="mt-2 text-3xl font-semibold text-fuchsia-600 dark:text-fuchsia-400">
                                        {{ $feedback['intonation'] }}/100
                                    </div>
                                    <div class="mt-3 h-2 rounded-full bg-fuchsia-100 dark:bg-fuchsia-950/60">
                                        <div
                                            class="h-2 rounded-full bg-fuchsia-500"
                                            style="width: {{ max(0, min(100, (int) $feedback['intonation'])) }}%;"
                                        ></div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-white/70 bg-white/90 p-4 dark:border-gray-700 dark:bg-gray-900/80">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Grammar
                                    </div>
                                    <div class="mt-2 text-3xl font-semibold text-emerald-600 dark:text-emerald-400">
                                        {{ $feedback['grammar'] }}/100
                                    </div>
                                    <div class="mt-3 h-2 rounded-full bg-emerald-100 dark:bg-emerald-950/60">
                                        <div
                                            class="h-2 rounded-full bg-emerald-500"
                                            style="width: {{ max(0, min(100, (int) $feedback['grammar'])) }}%;"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-xl border border-white/70 bg-white/90 p-4 dark:border-gray-700 dark:bg-gray-900/80 sm:p-5">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                                        Coach Notes
                                    </h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        Actionable feedback
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    @forelse ($feedbackParagraphs as $paragraph)
                                        <div class="rounded-lg border border-gray-200/80 bg-gray-50/80 p-3 text-sm leading-6 text-gray-700 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-200">
                                            {{ $paragraph }}
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            No detailed feedback text was returned for this analysis.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </section>
                    @endif

                    @if ($savedRecordingCount)
                        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-8">
                            <div class="mb-4 flex flex-col gap-1">
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Recording History</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Every attempt is saved. The detailed AI assessment appears in the panel above.
                                </p>
                            </div>

                            <div class="space-y-4">
                                @foreach ($savedRecordings as $recording)
                                    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/30">
                                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                                    Recording #{{ $recording['id'] }}
                                                </span>

                                                @if ($loop->first)
                                                    <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                        Latest
                                                    </span>
                                                @endif
                                            </div>

                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ $recording['created_at'] }}
                                            </span>
                                        </div>

                                        <audio controls class="mb-3 w-full" src="{{ $recording['audio_url'] }}"></audio>

                                        @if ($recording['analyzed_at'])
                                            <div class="grid grid-cols-3 gap-2">
                                                <div class="rounded-lg border border-gray-200 bg-white p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                                                    <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Pron.</div>
                                                    <div class="mt-1 text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                        {{ $recording['pronunciation_score'] }}/100
                                                    </div>
                                                </div>
                                                <div class="rounded-lg border border-gray-200 bg-white p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                                                    <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Inton.</div>
                                                    <div class="mt-1 text-sm font-semibold text-fuchsia-600 dark:text-fuchsia-400">
                                                        {{ $recording['intonation_score'] }}/100
                                                    </div>
                                                </div>
                                                <div class="rounded-lg border border-gray-200 bg-white p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                                                    <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Grammar</div>
                                                    <div class="mt-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                        {{ $recording['grammar_score'] }}/100
                                                    </div>
                                                </div>
                                            </div>

                                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                Analyzed {{ $recording['analyzed_at'] }}. Full coaching notes are shown in the Pronunciation Assessment panel.
                                            </p>
                                        @else
                                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                                Recording saved. Click <span class="font-medium">Analyze Recording</span> to get pronunciation feedback.
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @script
        <script>
            let mediaRecorder
            let audioChunks = []
            let recordedBlob = null
            let recordedMimeType = 'audio/webm'
            let localAudioUrl = null

            const root = $wire.$el

            const getElements = () => ({
                startRecordingBtn: root.querySelector('#startRecording'),
                stopRecordingBtn: root.querySelector('#stopRecording'),
                playRecordingBtn: root.querySelector('#playRecording'),
                analyzeRecordingBtn: root.querySelector('#analyzeRecording'),
                audioPlayback: root.querySelector('#audioPlayback'),
                recordingStatus: root.querySelector('#recordingStatus'),
            })

            const syncRecorderControls = () => {
                const { stopRecordingBtn, playRecordingBtn } = getElements()

                if (stopRecordingBtn) {
                    stopRecordingBtn.disabled = !mediaRecorder || mediaRecorder.state === 'inactive'
                }

                if (playRecordingBtn) {
                    playRecordingBtn.disabled = !recordedBlob
                }
            }

            const setStatus = (message = '') => {
                const { recordingStatus } = getElements()

                if (recordingStatus) {
                    recordingStatus.textContent = message
                }
            }

            const normalizeRecorderMimeType = (mimeType) => {
                const baseMimeType = String(mimeType || '')
                    .split(';', 1)[0]
                    .trim()
                    .toLowerCase()

                if (!baseMimeType) {
                    return 'audio/webm'
                }

                if (baseMimeType === 'video/webm') {
                    return 'audio/webm'
                }

                return baseMimeType
            }

            const getPreferredRecorderMimeType = () => {
                if (typeof MediaRecorder === 'undefined' || typeof MediaRecorder.isTypeSupported !== 'function') {
                    return null
                }

                const candidates = [
                    'audio/webm;codecs=opus',
                    'audio/webm',
                    'audio/ogg;codecs=opus',
                    'audio/ogg',
                ]

                return candidates.find((candidate) => MediaRecorder.isTypeSupported(candidate)) ?? null
            }

            const buildRecordingFile = () => {
                if (!recordedBlob || !recordedBlob.size) {
                    return null
                }

                const extensionByType = {
                    'audio/webm': 'webm',
                    'video/webm': 'webm',
                    'audio/ogg': 'ogg',
                    'audio/mpeg': 'mp3',
                    'audio/mp4': 'm4a',
                    'audio/wav': 'wav',
                }

                const normalizedMimeType = normalizeRecorderMimeType(recordedMimeType)
                const extension = extensionByType[normalizedMimeType] ?? 'webm'

                return new File(
                    [recordedBlob],
                    `reading-recording-${Date.now()}.${extension}`,
                    { type: normalizedMimeType || 'audio/webm' },
                )
            }

            const startRecording = async () => {
                const { startRecordingBtn, stopRecordingBtn, playRecordingBtn, audioPlayback } = getElements()

                if (!startRecordingBtn || !stopRecordingBtn || !playRecordingBtn || !audioPlayback) {
                    return
                }

                if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
                    alert('Audio recording is not supported in this browser.')
                    return
                }

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
                    const preferredMimeType = getPreferredRecorderMimeType()

                    mediaRecorder = preferredMimeType
                        ? new MediaRecorder(stream, { mimeType: preferredMimeType })
                        : new MediaRecorder(stream)
                    audioChunks = []
                    recordedBlob = null
                    setStatus('Recording...')

                    mediaRecorder.addEventListener('dataavailable', (event) => {
                        if (event.data.size > 0) {
                            audioChunks.push(event.data)
                        }
                    })

                    mediaRecorder.addEventListener('stop', () => {
                        recordedMimeType = normalizeRecorderMimeType(
                            mediaRecorder?.mimeType || preferredMimeType || 'audio/webm'
                        )
                        recordedBlob = new Blob(audioChunks, { type: recordedMimeType })

                        if (localAudioUrl) {
                            URL.revokeObjectURL(localAudioUrl)
                        }

                        localAudioUrl = URL.createObjectURL(recordedBlob)
                        audioPlayback.src = localAudioUrl
                        audioPlayback.style.display = 'block'
                        playRecordingBtn.disabled = false
                        setStatus('Recording ready. Click Analyze Recording to save and analyze it.')
                        syncRecorderControls()
                    }, { once: true })

                    mediaRecorder.start()
                    startRecordingBtn.disabled = true
                    stopRecordingBtn.disabled = false
                    playRecordingBtn.disabled = true
                } catch (error) {
                    console.error('Error accessing microphone:', error)
                    setStatus('')
                    alert('Could not access microphone. Please grant permission.')
                }
            }

            const stopRecording = () => {
                const { startRecordingBtn, stopRecordingBtn } = getElements()

                if (!startRecordingBtn || !stopRecordingBtn) {
                    return
                }

                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                        mediaRecorder.stop()
                        mediaRecorder.stream.getTracks().forEach((track) => track.stop())
                        startRecordingBtn.disabled = false
                        stopRecordingBtn.disabled = true
                        syncRecorderControls()
                    }
                }

            const playRecording = () => {
                const { audioPlayback } = getElements()

                if (!audioPlayback?.src) {
                    return
                }

                void audioPlayback.play()
            }

            const analyzeRecording = async () => {
                const { analyzeRecordingBtn } = getElements()
                const file = buildRecordingFile()

                if (!file) {
                    alert('Record audio first, then click Analyze Recording.')
                    return
                }

                if (analyzeRecordingBtn) {
                    analyzeRecordingBtn.disabled = true
                }

                setStatus('Uploading recording...')

                $wire.$upload(
                    'recordingUpload',
                    file,
                    async () => {
                        try {
                            setStatus('Analyzing recording with AI...')
                            await $wire.$call('analyzeRecording')
                            setStatus('Recording saved and analyzed.')
                        } catch (error) {
                            console.error('Error analyzing recording:', error)
                            setStatus('Recording upload finished, but analysis failed.')
                        } finally {
                            if (analyzeRecordingBtn) {
                                analyzeRecordingBtn.disabled = false
                            }
                        }
                    },
                    () => {
                        if (analyzeRecordingBtn) {
                            analyzeRecordingBtn.disabled = false
                        }

                        setStatus('Upload failed.')
                        alert('Could not upload the recorded audio.')
                    },
                    (event) => {
                        setStatus(`Uploading recording... ${event.detail.progress}%`)
                    },
                )
            }

            root.addEventListener('click', (event) => {
                const target = event.target instanceof Element ? event.target : null

                if (!target) {
                    return
                }

                if (target.closest('#startRecording')) {
                    event.preventDefault()
                    void startRecording()
                    return
                }

                if (target.closest('#stopRecording')) {
                    event.preventDefault()
                    stopRecording()
                    return
                }

                if (target.closest('#playRecording')) {
                    event.preventDefault()
                    playRecording()
                    return
                }

                if (target.closest('#analyzeRecording')) {
                    event.preventDefault()
                    void analyzeRecording()
                }
            })

            syncRecorderControls()
        </script>
    @endscript
</x-filament-panels::page>
