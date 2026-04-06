<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VoiceNoteUpload extends Component
{
    use WithFileUploads;

    public $voiceNote;
    public $isRecording = false;
    public $recordingTime = 0;
    public $maxRecordingTime = 300; // 5 minutes
    public $uploadProgress = 0;
    public $isUploading = false;
    public $uploadComplete = false;
    public $uploadMessage = '';
    public $showProgressBar = false;
    public $recordedAudio = null;
    public $componentId;
    public $name;
    public $required = false;
    public $maxFileSize = 10240; // 10MB

    protected $rules = [
        'voiceNote' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240'
    ];

    protected $messages = [
        'voiceNote.file' => 'Please select a valid audio file.',
        'voiceNote.mimes' => 'The voice note must be a file of type: mp3, wav, ogg, m4a',
        'voiceNote.max' => 'The voice note size must not exceed 10MB.'
    ];

    public function mount($name = 'voice_note', $required = false)
    {
        $this->name = $name;
        $this->required = $required;
        $this->componentId = uniqid('voice-note-');
    }

    public function startRecording()
    {
        $this->isRecording = true;
        $this->recordingTime = 0;
        $this->recordedAudio = null;
        $this->uploadComplete = false;
        
        $this->dispatch('start-recording');
    }

    public function stopRecording()
    {
        $this->isRecording = false;
        $this->dispatch('stop-recording');
    }

    public function updateRecordingTime()
    {
        if ($this->isRecording && $this->recordingTime < $this->maxRecordingTime) {
            $this->recordingTime++;
        } elseif ($this->recordingTime >= $this->maxRecordingTime) {
            $this->stopRecording();
        }
    }

    public function updatedVoiceNote()
    {
        $this->validate();
        
        if ($this->voiceNote) {
            $this->startVoiceNoteUpload();
        }
    }

    public function startVoiceNoteUpload()
    {
        $this->isUploading = true;
        $this->showProgressBar = true;
        $this->uploadComplete = false;
        $this->uploadMessage = 'Processing voice note...';
        $this->uploadProgress = 0;

        $this->processVoiceNoteUpload();
    }

    public function processVoiceNoteUpload()
    {
        try {
            // Simulate upload progress
            for ($progress = 10; $progress <= 90; $progress += 20) {
                $this->uploadProgress = $progress;
                $this->uploadMessage = "Uploading voice note... {$progress}%";
                usleep(200000); // 0.2 second delay for progress simulation
            }

            // Store the actual file
            $path = $this->voiceNote->store('temporary/voice-notes', 'local');
            
            if ($path) {
                $this->uploadProgress = 100;
                $this->uploadMessage = 'Voice note uploaded successfully! 🎵';
                $this->uploadComplete = true;
                
                // Store file info
                $this->recordedAudio = [
                    'path' => $path,
                    'name' => $this->voiceNote->getClientOriginalName(),
                    'size' => $this->voiceNote->getSize(),
                    'type' => $this->voiceNote->getMimeType(),
                    'duration' => $this->recordingTime,
                    'uploaded_at' => now()
                ];
                
                $this->dispatch('voice-note-uploaded', audioData: $this->recordedAudio);
            }
        } catch (\Exception $e) {
            Log::error("Voice note upload failed: " . $e->getMessage());
            $this->addError('voiceNote', "Failed to upload voice note: " . $e->getMessage());
        } finally {
            $this->isUploading = false;
            $this->showProgressBar = false;
        }
    }

    public function deleteVoiceNote()
    {
        if ($this->recordedAudio && isset($this->recordedAudio['path'])) {
            Storage::disk('local')->delete($this->recordedAudio['path']);
        }
        
        $this->recordedAudio = null;
        $this->voiceNote = null;
        $this->uploadComplete = false;
        $this->uploadProgress = 0;
        $this->recordingTime = 0;
        
        $this->dispatch('voice-note-deleted');
    }

    public function getFormattedRecordingTime()
    {
        $minutes = floor($this->recordingTime / 60);
        $seconds = $this->recordingTime % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFormattedFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function render()
    {
        return view('livewire.voice-note-upload');
    }
}
