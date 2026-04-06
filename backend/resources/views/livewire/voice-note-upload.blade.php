<div class="voice-note-upload-component" id="{{ $componentId }}">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bx bx-microphone me-2"></i>
                Voice Note
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            <!-- Recording Controls -->
            <div class="mb-3">
                <div class="d-flex gap-2 mb-3">
                    <button type="button" 
                            class="btn btn-primary" 
                            wire:click="startRecording"
                            wire:loading.attr="disabled"
                            wire:target="startRecording"
                            {{ $isRecording ? 'disabled' : '' }}>
                        <i class="bx bx-microphone me-2"></i>
                        <span wire:loading.remove wire:target="startRecording">Start Recording</span>
                        <span wire:loading wire:target="startRecording">Starting...</span>
                    </button>
                    
                    <button type="button" 
                            class="btn btn-secondary" 
                            wire:click="stopRecording"
                            wire:loading.attr="disabled"
                            wire:target="stopRecording"
                            {{ !$isRecording ? 'disabled' : '' }}>
                        <i class="bx bx-stop-circle me-2"></i>
                        <span wire:loading.remove wire:target="stopRecording">Stop Recording</span>
                        <span wire:loading wire:target="stopRecording">Stopping...</span>
                    </button>
                </div>

                <!-- Recording Timer -->
                @if($isRecording)
                    <div class="alert alert-info d-flex align-items-center">
                        <div class="recording-indicator me-2"></div>
                        <span>Recording in progress... {{ $this->getFormattedRecordingTime() }}</span>
                    </div>
                @endif

                <!-- Recording Time Display -->
                @if($recordingTime > 0)
                    <div class="text-center mb-3">
                        <h4 class="text-primary mb-0">{{ $this->getFormattedRecordingTime() }}</h4>
                        <small class="text-muted">Recording Duration</small>
                    </div>
                @endif
            </div>

            <!-- File Upload Alternative -->
            <div class="mb-3">
                <label class="form-label">Or upload an audio file:</label>
                <input type="file" 
                       wire:model="voiceNote" 
                       class="form-control @error('voiceNote') is-invalid @enderror"
                       name="{{ $name }}"
                       accept="audio/*"
                       id="voice-note-upload-{{ $componentId }}">
                
                @error('voiceNote')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <small class="form-text text-muted">
                    Maximum {{ $maxFileSize/1024/1024 }}MB. Supported: MP3, WAV, OGG, M4A
                </small>
            </div>

            <!-- Progress Bar -->
            @if($showProgressBar && $isUploading)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-primary fw-bold">{{ $uploadMessage }}</span>
                        <span class="badge bg-primary">{{ $uploadProgress }}%</span>
                    </div>
                    
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ $uploadProgress }}%"
                             aria-valuenow="{{ $uploadProgress }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Success Message -->
            @if($uploadComplete)
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ $uploadMessage }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Audio Player -->
            @if($recordedAudio)
                <div class="audio-player-section">
                    <h6 class="mb-3">Uploaded Voice Note</h6>
                    
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bx bx-music fs-1 text-success me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $recordedAudio['name'] }}</h6>
                                    <small class="text-muted">
                                        {{ $this->formatFileSize($recordedAudio['size']) }} • 
                                        @if($recordedAudio['duration'])
                                            {{ $this->formatDuration($recordedAudio['duration']) }}
                                        @else
                                            Unknown duration
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Audio Controls -->
                            <div class="mb-3">
                                <audio controls class="w-100">
                                    <source src="{{ Storage::disk('local')->url($recordedAudio['path']) }}" type="{{ $recordedAudio['type'] }}">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>

                            <!-- File Info -->
                            <div class="row text-center">
                                <div class="col">
                                    <small class="text-muted d-block">Type</small>
                                    <span class="badge bg-info">{{ strtoupper(pathinfo($recordedAudio['name'], PATHINFO_EXTENSION)) }}</span>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Size</small>
                                    <span class="fw-bold">{{ $this->formatFileSize($recordedAudio['size']) }}</span>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Uploaded</small>
                                    <span class="fw-bold">{{ $recordedAudio['uploaded_at'] ? $recordedAudio['uploaded_at']->diffForHumans() : 'Just now' }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-3 text-center">
                                <button type="button" 
                                        class="btn btn-outline-danger btn-sm" 
                                        wire:click="deleteVoiceNote">
                                    <i class="bx bx-trash me-1"></i>
                                    Delete Voice Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recording Tips -->
            <div class="mt-3 p-3 bg-light rounded">
                <h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Recording Tips</h6>
                <ul class="mb-0 small text-muted">
                    <li>Speak clearly and at a normal pace</li>
                    <li>Record in a quiet environment</li>
                    <li>Maximum recording time: {{ $this->formatDuration($maxRecordingTime) }}</li>
                    <li>You can also upload pre-recorded audio files</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .recording-indicator {
            width: 12px;
            height: 12px;
            background-color: #dc3545;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .audio-player-section {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        
        .progress {
            border-radius: 10px;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
    </style>

    <script>
        // Auto-hide success message after 5 seconds
        document.addEventListener('livewire:init', () => {
            Livewire.on('hide-success-message', () => {
                setTimeout(() => {
                    const alerts = document.querySelectorAll('.alert-success');
                    alerts.forEach(alert => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });

            // Update recording time every second when recording
            Livewire.on('start-recording', () => {
                const interval = setInterval(() => {
                    @this.updateRecordingTime();
                }, 1000);

                // Store interval ID to clear it later
                window.recordingInterval = interval;
            });

            Livewire.on('stop-recording', () => {
                if (window.recordingInterval) {
                    clearInterval(window.recordingInterval);
                    window.recordingInterval = null;
                }
            });
        });
    </script>
</div>
