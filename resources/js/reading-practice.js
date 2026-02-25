
if (! window.__readingPracticeRecorderBound) {
    window.__readingPracticeRecorderBound = true;

    let mediaRecorder;
    let audioChunks = [];
    let audioBlob;

    const getElements = () => ({
        startRecordingBtn: document.getElementById('startRecording'),
        stopRecordingBtn: document.getElementById('stopRecording'),
        playRecordingBtn: document.getElementById('playRecording'),
        audioPlayback: document.getElementById('audioPlayback'),
    });

    const startRecording = async () => {
        const { startRecordingBtn, stopRecordingBtn, playRecordingBtn, audioPlayback } = getElements();

        if (! startRecordingBtn || ! stopRecordingBtn || ! playRecordingBtn || ! audioPlayback) {
            return;
        }

        if (! navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
            alert('Audio recording is not supported in this browser.');

            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.addEventListener('dataavailable', (event) => {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            });

            mediaRecorder.addEventListener('stop', () => {
                audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                const audioUrl = URL.createObjectURL(audioBlob);

                audioPlayback.src = audioUrl;
                audioPlayback.style.display = 'block';
                playRecordingBtn.disabled = false;
            }, { once: true });

            mediaRecorder.start();
            startRecordingBtn.disabled = true;
            stopRecordingBtn.disabled = false;
            playRecordingBtn.disabled = true;
        } catch (error) {
            console.error('Error accessing microphone:', error);
            alert('Could not access microphone. Please grant permission.');
        }
    };

    const stopRecording = () => {
        const { startRecordingBtn, stopRecordingBtn } = getElements();

        if (! startRecordingBtn || ! stopRecordingBtn) {
            return;
        }

        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach((track) => track.stop());
            startRecordingBtn.disabled = false;
            stopRecordingBtn.disabled = true;
        }
    };

    const playRecording = () => {
        const { audioPlayback } = getElements();

        if (! audioPlayback || ! audioPlayback.src) {
            return;
        }

        void audioPlayback.play();
    };

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (! target) {
            return;
        }

        if (target.closest('#startRecording')) {
            event.preventDefault();
            void startRecording();

            return;
        }

        if (target.closest('#stopRecording')) {
            event.preventDefault();
            stopRecording();

            return;
        }

        if (target.closest('#playRecording')) {
            event.preventDefault();
            playRecording();
        }
    });
}
