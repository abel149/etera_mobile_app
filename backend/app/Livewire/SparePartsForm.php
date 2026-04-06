<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class SparePartsForm extends Component
{
    use WithFileUploads;

    public $parts = [];
    public $maxParts = 20;

    protected $rules = [
        'parts.*.number' => 'required|string|max:255',
        'parts.*.grade' => 'required|string|in:1st grade (Original OEM),2nd grade (After market),3rd grade',
        'parts.*.country' => 'nullable|string|max:255',
        'parts.*.quantity' => 'required|integer|min:1',
        'parts.*.component' => 'required|string|in:Body Parts,Mechanical Parts',
        'parts.*.images' => 'nullable|array',
        'parts.*.images.*' => 'image|max:2048|mimes:jpeg,png,jpg,webp'
    ];

    protected $messages = [
        'parts.*.number.required' => 'Part number is required.',
        'parts.*.grade.required' => 'Please select a grade.',
        'parts.*.quantity.required' => 'Quantity is required.',
        'parts.*.quantity.integer' => 'Quantity must be a number.',
        'parts.*.quantity.min' => 'Quantity must be at least 1.',
        'parts.*.component.required' => 'Please select a component type.',
        'parts.*.images.*.image' => 'The file must be an image.',
        'parts.*.images.*.max' => 'The image size must not exceed 2MB.',
        'parts.*.images.*.mimes' => 'The image must be a file of type: jpeg, png, jpg, webp.'
    ];

    public function mount()
    {
        // Initialize with one empty part
        $this->addPart();
    }

    public function addPart()
    {
        if (count($this->parts) < $this->maxParts) {
            $this->parts[] = [
                'number' => '',
                'grade' => '',
                'country' => '',
                'quantity' => 1,
                'component' => '',
                'images' => [],
                'temporaryImages' => []
            ];
        }
    }

    public function removePart($index)
    {
        if (isset($this->parts[$index])) {
            // Clean up any temporary images
            if (isset($this->parts[$index]['temporaryImages'])) {
                foreach ($this->parts[$index]['temporaryImages'] as $image) {
                    if (isset($image['path'])) {
                        \Storage::disk('local')->delete($image['path']);
                    }
                }
            }
            unset($this->parts[$index]);
        }
        
        // Re-index array
        $this->parts = array_values($this->parts);
    }

    public function updatedParts($value, $key)
    {
        // Handle image uploads for specific parts
        if (str_contains($key, 'images')) {
            $partIndex = explode('.', $key)[0];
            $this->handlePartImages($partIndex);
        }
    }

    protected function handlePartImages($partIndex)
    {
        if (isset($this->parts[$partIndex]['images'])) {
            $images = $this->parts[$partIndex]['images'];
            $temporaryImages = [];
            
            foreach ($images as $image) {
                if ($image) {
                    $path = $image->store('temporary/spare-parts', 'local');
                    $temporaryImages[] = [
                        'path' => $path,
                        'name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'type' => $image->getMimeType()
                    ];
                }
            }
            
            $this->parts[$partIndex]['temporaryImages'] = $temporaryImages;
        }
    }

    public function removePartImage($partIndex, $imageIndex)
    {
        if (isset($this->parts[$partIndex]['temporaryImages'][$imageIndex])) {
            $image = $this->parts[$partIndex]['temporaryImages'][$imageIndex];
            if (isset($image['path'])) {
                \Storage::disk('local')->delete($image['path']);
            }
            unset($this->parts[$partIndex]['temporaryImages'][$imageIndex]);
            $this->parts[$partIndex]['temporaryImages'] = array_values($this->parts[$partIndex]['temporaryImages']);
        }
    }

    public function clearPartImages($partIndex)
    {
        if (isset($this->parts[$partIndex]['temporaryImages'])) {
            foreach ($this->parts[$partIndex]['temporaryImages'] as $image) {
                if (isset($image['path'])) {
                    \Storage::disk('local')->delete($image['path']);
                }
            }
            $this->parts[$partIndex]['temporaryImages'] = [];
        }
        $this->parts[$partIndex]['images'] = [];
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function validateParts()
    {
        $this->validate();
        return true;
    }

    public function render()
    {
        return view('livewire.spare-parts-form');
    }
}
