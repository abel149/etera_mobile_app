<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class FileUpload extends Component
{
    use WithFileUploads;

    public $files = [];
    public $temporaryFiles = [];
    public $maxFiles = 10;
    public $maxFileSize = 5120; // 5MB
    public $acceptedTypes = [];
    public $uploadLabel = 'Upload Files';
    public $componentId;
    public $name;
    public $multiple = true;
    public $required = false;
    public $showPreview = true;
    public $fileType = 'all'; // 'all', 'image', 'video', 'audio', 'document'
    public $previewSize = '100px';

    protected $rules = [
        'files.*' => 'max:5120'
    ];

    protected $messages = [
        'files.*.max' => 'The file size must not exceed 5MB.'
    ];

    public function mount($name = 'files', $fileType = 'all', $multiple = true, $required = false, $maxFiles = 10, $showPreview = true)
    {
        $this->name = $name;
        $this->fileType = $fileType;
        $this->multiple = $multiple;
        $this->required = $required;
        $this->maxFiles = $maxFiles;
        $this->showPreview = $showPreview;
        $this->componentId = uniqid('file-upload-');
        
        $this->setAcceptedTypes();
    }

    protected function setAcceptedTypes()
    {
        switch ($this->fileType) {
            case 'image':
                $this->acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
                $this->rules['files.*'] = 'image|max:5120|mimes:jpeg,png,jpg,webp,gif';
                break;
            case 'video':
                $this->acceptedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv'];
                $this->rules['files.*'] = 'mimes:mp4,avi,mov,wmv,flv|max:51200'; // 50MB for videos
                break;
            case 'audio':
                $this->acceptedTypes = ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'];
                $this->rules['files.*'] = 'mimes:mp3,wav,ogg,m4a|max:10240'; // 10MB for audio
                break;
            case 'document':
                $this->acceptedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                $this->rules['files.*'] = 'mimes:pdf,doc,docx,txt|max:10240'; // 10MB for documents
                break;
            default: // 'all'
                $this->acceptedTypes = [
                    'image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif',
                    'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv',
                    'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a',
                    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'
                ];
                break;
        }
    }

    public function updatedFiles()
    {
        $this->validate();
        
        if (count($this->files) > $this->maxFiles) {
            $this->addError('files', "Maximum {$this->maxFiles} files allowed.");
            return;
        }

        // Store files temporarily and get their paths
        foreach ($this->files as $file) {
            if ($file) {
                $path = $file->store('temporary/livewire', 'local');
                $this->temporaryFiles[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension()
                ];
            }
        }
    }

    public function removeFile($index)
    {
        if (isset($this->temporaryFiles[$index])) {
            // Delete temporary file
            Storage::disk('local')->delete($this->temporaryFiles[$index]['path']);
            unset($this->temporaryFiles[$index]);
        }
        
        if (isset($this->files[$index])) {
            unset($this->files[$index]);
        }
        
        // Re-index arrays
        $this->temporaryFiles = array_values($this->temporaryFiles);
        $this->files = array_values($this->files);
    }

    public function clearAll()
    {
        // Delete all temporary files
        foreach ($this->temporaryFiles as $file) {
            Storage::disk('local')->delete($file['path']);
        }
        
        $this->temporaryFiles = [];
        $this->files = [];
    }

    public function getUploadedFiles()
    {
        return $this->temporaryFiles;
    }

    public function getFileIcon($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'bx bx-image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'bx bx-video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'bx bx-music';
        } elseif (str_starts_with($mimeType, 'application/pdf')) {
            return 'bx bx-file-pdf';
        } elseif (str_starts_with($mimeType, 'application/msword') || str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) {
            return 'bx bx-file-doc';
        } elseif (str_starts_with($mimeType, 'text/')) {
            return 'bx bx-file-txt';
        } else {
            return 'bx bx-file';
        }
    }

    public function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
