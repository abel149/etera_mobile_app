<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RoleBasedMediaViewer extends Component
{
    public $proformaId;
    public $mediaData = [];
    public $userRole;
    public $showImages = false;
    public $showVoiceNotes = false;
    public $showStampImages = false;
    public $showAllMedia = false;

    public function mount($proformaId = null)
    {
        $this->proformaId = $proformaId;
        $this->userRole = Auth::user()?->role ?? 'guest';
        $this->setMediaVisibility();
        $this->loadMediaData();
    }

    public function setMediaVisibility()
    {
        switch ($this->userRole) {
            case 'admin':
            case 'superadmin':
                // Admin can see everything
                $this->showImages = true;
                $this->showVoiceNotes = true;
                $this->showStampImages = true;
                $this->showAllMedia = true;
                break;
                
            case 'insurance':
                // Insurance can see stamp images and basic info
                $this->showImages = false;
                $this->showVoiceNotes = false;
                $this->showStampImages = true;
                $this->showAllMedia = false;
                break;
                
            case 'shop':
            case 'garage':
                // Shops and garages can see images and voice notes
                $this->showImages = true;
                $this->showVoiceNotes = true;
                $this->showStampImages = false;
                $this->showAllMedia = false;
                break;
                
            case 'business_owner':
                // Business owners can see images and voice notes
                $this->showImages = true;
                $this->showVoiceNotes = true;
                $this->showStampImages = false;
                $this->showAllMedia = false;
                break;
                
            default:
                // Other roles see limited media
                $this->showImages = false;
                $this->showVoiceNotes = false;
                $this->showStampImages = false;
                $this->showAllMedia = false;
                break;
        }
    }

    public function loadMediaData()
    {
        if (!$this->proformaId) {
            return;
        }

        // Load media data based on proforma
        $this->mediaData = $this->getProformaMedia();
    }

    public function getProformaMedia()
    {
        // This would typically load from your database
        // For now, returning sample data structure
        return [
            'images' => $this->showImages ? $this->getProformaImages() : [],
            'voice_notes' => $this->showVoiceNotes ? $this->getProformaVoiceNotes() : [],
            'stamp_images' => $this->showStampImages ? $this->getProformaStampImages() : [],
            'documents' => $this->showAllMedia ? $this->getProformaDocuments() : []
        ];
    }

    private function getProformaImages()
    {
        // Sample image data - replace with actual database query
        return [
            [
                'id' => 1,
                'path' => 'proformas/images/part1.jpg',
                'name' => 'Spare Part Image 1',
                'type' => 'image/jpeg',
                'size' => 1024000,
                'uploaded_at' => now()->subHours(2),
                'uploaded_by' => 'Spare Parts Shop A'
            ],
            [
                'id' => 2,
                'path' => 'proformas/images/part2.jpg',
                'name' => 'Spare Part Image 2',
                'type' => 'image/png',
                'size' => 2048000,
                'uploaded_at' => now()->subHours(1),
                'uploaded_by' => 'Garage B'
            ]
        ];
    }

    private function getProformaVoiceNotes()
    {
        // Sample voice note data - replace with actual database query
        return [
            [
                'id' => 1,
                'path' => 'proformas/voice-notes/note1.mp3',
                'name' => 'Voice Note 1',
                'type' => 'audio/mp3',
                'size' => 5120000,
                'duration' => 45,
                'uploaded_at' => now()->subHours(3),
                'uploaded_by' => 'Spare Parts Shop A'
            ],
            [
                'id' => 2,
                'path' => 'proformas/voice-notes/note2.wav',
                'name' => 'Voice Note 2',
                'type' => 'audio/wav',
                'size' => 10240000,
                'duration' => 120,
                'uploaded_at' => now()->subHours(2),
                'uploaded_by' => 'Garage B'
            ]
        ];
    }

    private function getProformaStampImages()
    {
        // Sample stamp image data - replace with actual database query
        return [
            [
                'id' => 1,
                'path' => 'proformas/stamps/stamp1.jpg',
                'name' => 'Business Stamp 1',
                'type' => 'image/jpeg',
                'size' => 512000,
                'uploaded_at' => now()->subDays(1),
                'uploaded_by' => 'Insurance Company A'
            ],
            [
                'id' => 2,
                'path' => 'proformas/stamps/stamp2.png',
                'name' => 'Business Stamp 2',
                'type' => 'image/png',
                'size' => 1024000,
                'uploaded_at' => now()->subDays(2),
                'uploaded_by' => 'Insurance Company B'
            ]
        ];
    }

    private function getProformaDocuments()
    {
        // Sample document data - replace with actual database query
        return [
            [
                'id' => 1,
                'path' => 'proformas/documents/doc1.pdf',
                'name' => 'Technical Specification',
                'type' => 'application/pdf',
                'size' => 2048000,
                'uploaded_at' => now()->subHours(4),
                'uploaded_by' => 'Admin User'
            ]
        ];
    }

    public function getFileIcon($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'bx bx-image text-primary';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'bx bx-music text-success';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'bx bx-video text-danger';
        } elseif (str_starts_with($mimeType, 'application/pdf')) {
            return 'bx bx-file-pdf text-danger';
        } elseif (str_starts_with($mimeType, 'application/msword') || str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) {
            return 'bx bx-file-doc text-primary';
        } else {
            return 'bx bx-file text-secondary';
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

    public function formatDuration($seconds)
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    public function canViewMedia($mediaType)
    {
        switch ($mediaType) {
            case 'images':
                return $this->showImages;
            case 'voice_notes':
                return $this->showVoiceNotes;
            case 'stamp_images':
                return $this->showStampImages;
            case 'documents':
                return $this->showAllMedia;
            default:
                return false;
        }
    }

    public function render()
    {
        return view('livewire.role-based-media-viewer');
    }
}
