@props(['type' => 'image', 'multiple' => true, 'maxFiles' => 10, 'acceptedTypes' => 'image/*'])

<div class="file-upload-container" data-type="{{ $type }}" data-multiple="{{ $multiple ? 'true' : 'false' }}">
    <!-- FilePond Container -->
    <div class="filepond-container">
        <input type="file" 
               class="filepond" 
               name="{{ $type }}[]" 
               id="{{ $type }}_upload"
               data-max-files="{{ $maxFiles }}"
               data-accepted-types="{{ $acceptedTypes }}"
               {{ $multiple ? 'multiple' : '' }}>
    </div>

    <!-- Voice Recording Section (only for audio type) -->
    @if($type === 'audio')
    <div class="voice-recorder-container mt-3">
        <div class="row">
            <div class="col-md-6">
                <h6>Voice Recording</h6>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-primary btn-sm" id="startRecording">
                        <i class="fas fa-microphone"></i> Start Recording
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="stopRecording" disabled>
                        <i class="fas fa-stop"></i> Stop Recording
                    </button>
                </div>
                
                <!-- Recording Status -->
                <div id="recordingStatus" style="display: none;" class="mb-2">
                    <span class="recording-indicator"></span>
                    <span class="ms-2">Recording...</span>
                </div>
                
                <!-- Audio Preview -->
                <div id="audioPreview" style="display: none;">
                    <audio controls class="w-100">
                        <source id="recordedAudio" type="audio/webm">
                    </audio>
                    <button type="button" class="btn btn-danger btn-sm mt-2" id="deleteRecording">
                        <i class="fas fa-trash"></i> Delete Recording
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Hidden input for voice note -->
        <input type="hidden" name="voice_note" id="voiceNoteInput">
    </div>
    @endif

    <!-- Upload Progress -->
    <div class="upload-progress mt-3" style="display: none;">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
        <small class="text-muted">Uploading files...</small>
    </div>

    <!-- Error Messages -->
    <div class="upload-errors mt-2" style="display: none;">
        <div class="alert alert-danger"></div>
    </div>
</div>

@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
<style>
.file-upload-container {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    background-color: #f8f9fa;
}

.filepond--root {
    margin-bottom: 0;
}

.filepond--panel-root {
    background-color: transparent;
    border: 1px solid #dee2e6;
    border-radius: 6px;
}

.filepond--drop-label {
    color: #6c757d;
    font-size: 14px;
}

.recording-indicator {
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    display: inline-block;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.5; }
    100% { transform: scale(1); opacity: 1; }
}

.voice-recorder-container {
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
}

.upload-progress {
    margin-top: 15px;
}

.upload-errors .alert {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.file-upload-container[data-type="{{ $type }}"]');
    if (!container) return;

    const type = container.dataset.type;
    const multiple = container.dataset.multiple === 'true';
    const maxFiles = parseInt(container.querySelector('.filepond').dataset.maxFiles) || 10;
    const acceptedTypes = container.querySelector('.filepond').dataset.acceptedTypes || '*/*';

    // Register FilePond plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize
    );

    // Create FilePond instance
    const pond = FilePond.create(container.querySelector('.filepond'), {
        allowMultiple: multiple,
        maxFiles: maxFiles,
        acceptedFileTypes: acceptedTypes.split(','),
        maxFileSize: '10MB',
        server: {
            url: '/upload-temp',
            process: {
                url: '/upload-temp',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                onload: (response) => {
                    try {
                        return JSON.parse(response).folder;
                    } catch (e) {
                        return response;
                    }
                }
            },
            revert: {
                url: '/upload-temp',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        },
        onprocessfile: (error, file) => {
            if (error) {
                console.error('File upload error:', error);
                showError('Error uploading file: ' + error);
            } else {
                console.log('File uploaded successfully:', file.filename);
            }
        },
        onprocessfiles: () => {
            hideProgress();
        },
        onaddfile: () => {
            showProgress();
        }
    });

    // Voice recording functionality (only for audio type)
    @if($type === 'audio')
    let mediaRecorder;
    let audioChunks = [];
    const startButton = document.getElementById('startRecording');
    const stopButton = document.getElementById('stopRecording');
    const recordingStatus = document.getElementById('recordingStatus');
    const audioPreview = document.getElementById('audioPreview');
    const recordedAudio = document.getElementById('recordedAudio');
    const deleteButton = document.getElementById('deleteRecording');
    const voiceNoteInput = document.getElementById('voiceNoteInput');

    if (startButton && stopButton) {
        startButton.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    } 
                });

                mediaRecorder = new MediaRecorder(stream, {
                    mimeType: 'audio/webm;codecs=opus'
                });

                audioChunks = [];

                mediaRecorder.addEventListener('dataavailable', event => {
                    audioChunks.push(event.data);
                });

                mediaRecorder.addEventListener('start', () => {
                    startButton.disabled = true;
                    stopButton.disabled = false;
                    recordingStatus.style.display = 'block';
                    audioPreview.style.display = 'none';
                });

                mediaRecorder.addEventListener('stop', () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm;codecs=opus' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    recordedAudio.src = audioUrl;
                    
                    // Convert blob to base64 for form submission
                    const reader = new FileReader();
                    reader.readAsDataURL(audioBlob);
                    reader.onloadend = function() {
                        voiceNoteInput.value = reader.result;
                    }

                    startButton.disabled = false;
                    stopButton.disabled = true;
                    recordingStatus.style.display = 'none';
                    audioPreview.style.display = 'block';
                });

                mediaRecorder.start(1000);
            } catch (err) {
                console.error('Error accessing microphone:', err);
                showError('Error accessing microphone: ' + err.message);
            }
        });

        stopButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
        });

        deleteButton.addEventListener('click', () => {
            voiceNoteInput.value = '';
            audioPreview.style.display = 'none';
            recordedAudio.src = '';
        });
    }
    @endif

    function showProgress() {
        container.querySelector('.upload-progress').style.display = 'block';
    }

    function hideProgress() {
        container.querySelector('.upload-progress').style.display = 'none';
    }

    function showError(message) {
        const errorContainer = container.querySelector('.upload-errors');
        const errorAlert = errorContainer.querySelector('.alert');
        errorAlert.textContent = message;
        errorContainer.style.display = 'block';
        
        setTimeout(() => {
            errorContainer.style.display = 'none';
        }, 5000);
    }
});
</script>
@endpush
