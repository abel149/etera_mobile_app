<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TemporaryFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageUpload extends Component
{
    use WithFileUploads;

    public $images = [];
    public $temporaryImages = [];
    public $maxFiles = 10;
    public $maxFileSize = 5120; // 5MB
    public $acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
    public $uploadLabel = 'Upload Images';
    public $componentId;
    public $name;
    public $multiple = true;
    public $required = false;
    public $showPreview = true;
    public $previewSize = '100px';
    
    // Progress tracking
    public $uploadProgress = [];
    public $isUploading = false;
    public $uploadComplete = false;
    public $uploadMessage = '';
    public $showProgressBar = false;

    protected $rules = [
        'images.*' => 'image|max:5120|mimes:jpeg,png,jpg,webp,gif'
    ];

    protected $messages = [
        'images.*.image' => 'The file must be an image.',
        'images.*.max' => 'The image size must not exceed 5MB.',
        'images.*.mimes' => 'The image must be a file of type: jpeg, png, jpg, webp, gif'
    ];

    public function mount($name = 'images', $multiple = true, $required = false, $maxFiles = 10, $showPreview = true)
    {
        $this->name = $name;
        $this->multiple = $multiple;
        $this->required = $required;
        $this->maxFiles = $maxFiles;
        $this->showPreview = $showPreview;
        $this->componentId = uniqid('image-upload-');
    }

    public function updatedImages()
    {
        $this->validate();
        
        if (count($this->images) > $this->maxFiles) {
            $this->addError('images', "Maximum {$this->maxFiles} images allowed.");
            return;
        }

        $this->startUpload();
    }

    public function startUpload()
    {
        $this->isUploading = true;
        $this->showProgressBar = true;
        $this->uploadComplete = false;
        $this->uploadMessage = 'Starting upload...';
        
        // Initialize progress for each file
        $this->uploadProgress = [];
        foreach ($this->images as $index => $image) {
            if ($image) {
                $this->uploadProgress[$index] = 0;
            }
        }

        $this->processUploads();
    }

    public function processUploads()
    {
        $totalFiles = count($this->images);
        $processedFiles = 0;

        foreach ($this->images as $index => $image) {
            if ($image) {
                try {
                    // Simulate progress for each file
                    $this->uploadProgress[$index] = 10;
                    $this->uploadMessage = "Processing {$image->getClientOriginalName()}...";
                    
                    // Store image with progress simulation
                    $path = $this->storeImageWithProgress($image, $index);
                    
                    if ($path) {
                        $this->temporaryImages[] = [
                            'path' => $path,
                            'name' => $image->getClientOriginalName(),
                            'size' => $image->getSize(),
                            'type' => $image->getMimeType(),
                            'uploaded_at' => now()
                        ];
                        
                        $this->uploadProgress[$index] = 100;
                        $processedFiles++;
                        
                        $this->uploadMessage = "Uploaded {$processedFiles} of {$totalFiles} files...";
                    }
                } catch (\Exception $e) {
                    Log::error("Image upload failed: " . $e->getMessage());
                    $this->addError('images', "Failed to upload {$image->getClientOriginalName()}: " . $e->getMessage());
                }
            }
        }

        $this->finishUpload();
    }

    private function storeImageWithProgress($image, $index)
    {
        // Simulate upload progress
        for ($progress = 10; $progress <= 90; $progress += 20) {
            $this->uploadProgress[$index] = $progress;
            usleep(100000); // 0.1 second delay for progress simulation
        }

        // Store the actual file
        $path = $image->store('temporary/livewire', 'local');
        
        return $path;
    }

    private function finishUpload()
    {
        $this->isUploading = false;
        $this->uploadComplete = true;
        $this->uploadMessage = 'Upload successful! 🎉';
        
        // Hide progress bar after 3 seconds
        $this->dispatch('upload-complete', message: $this->uploadMessage);
        
        // Auto-hide success message after 5 seconds
        $this->dispatch('hide-success-message');
    }

    public function removeImage($index)
    {
        if (isset($this->temporaryImages[$index])) {
            // Delete temporary file
            Storage::disk('local')->delete($this->temporaryImages[$index]['path']);
            unset($this->temporaryImages[$index]);
        }
        
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
        }
        
        // Re-index arrays
        $this->temporaryImages = array_values($this->temporaryImages);
        $this->images = array_values($this->images);
        
        // Reset progress
        if (isset($this->uploadProgress[$index])) {
            unset($this->uploadProgress[$index]);
        }
    }

    public function clearAll()
    {
        // Delete all temporary files
        foreach ($this->temporaryImages as $image) {
            Storage::disk('local')->delete($image['path']);
        }
        
        $this->temporaryImages = [];
        $this->images = [];
        $this->uploadProgress = [];
        $this->uploadComplete = false;
        $this->showProgressBar = false;
        $this->uploadMessage = '';
    }

    public function getUploadedImages()
    {
        return $this->temporaryImages;
    }

    public function getTotalProgress()
    {
        if (empty($this->uploadProgress)) {
            return 0;
        }
        
        $totalProgress = array_sum($this->uploadProgress);
        $totalFiles = count($this->uploadProgress);
        
        return $totalFiles > 0 ? round($totalProgress / $totalFiles) : 0;
    }

    public function render()
    {
        return view('livewire.image-upload');
    }
}
