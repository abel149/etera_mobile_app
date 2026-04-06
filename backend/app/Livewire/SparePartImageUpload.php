<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class SparePartImageUpload extends Component
{
    use WithFileUploads;

    public $images = [];
    public $temporaryImages = [];
    public $maxFiles = 5;
    public $maxFileSize = 2048; // 2MB
    public $acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    public $uploadLabel = 'Upload Part Images';
    public $componentId;
    public $name;
    public $partIndex;
    public $required = false;
    public $showPreview = true;
    public $previewSize = '80px';
    public $compactMode = false;

    protected $rules = [
        'images.*' => 'image|max:2048|mimes:jpeg,png,jpg,webp'
    ];

    protected $messages = [
        'images.*.image' => 'The file must be an image.',
        'images.*.max' => 'The image size must not exceed 2MB.',
        'images.*.mimes' => 'The image must be a file of type: jpeg, png, jpg, webp.'
    ];

    // Livewire 3: listeners are now handled differently, using dispatch and event listeners

    public function mount($name = 'part_images', $partIndex = 0, $required = false, $maxFiles = 5, $showPreview = true, $compactMode = false)
    {
        $this->name = $name;
        $this->partIndex = $partIndex;
        $this->required = $required;
        $this->maxFiles = $maxFiles;
        $this->showPreview = $showPreview;
        $this->compactMode = $compactMode;
        $this->componentId = "spare-part-{$partIndex}-" . uniqid();
    }

    public function updatedImages()
    {
        $this->validate();
        
        if (count($this->images) > $this->maxFiles) {
            $this->addError('images', "Maximum {$this->maxFiles} images allowed.");
            return;
        }

        // Store images temporarily and get their paths
        foreach ($this->images as $image) {
            if ($image) {
                $path = $image->store('temporary/spare-parts', 'local');
                $this->temporaryImages[] = [
                    'path' => $path,
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'type' => $image->getMimeType()
                ];
            }
        }

        // Dispatch event to parent component
        $this->dispatch('imagesUpdated', partIndex: $this->partIndex, temporaryImages: $this->temporaryImages);
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

        // Dispatch event to parent component
        $this->dispatch('imagesUpdated', partIndex: $this->partIndex, temporaryImages: $this->temporaryImages);
    }

    public function clearAll()
    {
        // Delete all temporary files
        foreach ($this->temporaryImages as $image) {
            Storage::disk('local')->delete($image['path']);
        }
        
        $this->temporaryImages = [];
        $this->images = [];

        // Dispatch event to parent component
        $this->dispatch('imagesUpdated', partIndex: $this->partIndex, temporaryImages: $this->temporaryImages);
    }

    public function getUploadedImages()
    {
        return $this->temporaryImages;
    }

    public function setImages($images)
    {
        $this->temporaryImages = $images;
    }

    public function render()
    {
        return view('livewire.spare-part-image-upload');
    }
}
