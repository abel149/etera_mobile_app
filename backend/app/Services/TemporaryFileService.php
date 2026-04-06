<?php

namespace App\Services;

use App\Models\TemporaryFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TemporaryFileService
{
    public function storeFile($file, $type)
    {
        try {
            $name = $file->getClientOriginalName();
            $folder = uniqid('temp', true);
            $file->storeAs('temporary/tmp/' . $folder, $name);

            $tempFile = TemporaryFile::create([
                'folder' => $folder,
                'file' => $name,
            ]);

            Log::info("Temporary file stored", [
                'folder' => $folder,
                'file' => $name,
                'type' => $type
            ]);

            return $folder;

        } catch (\Exception $e) {
            Log::error("Error storing temporary file: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteFile($folder)
    {
        try {
            $temp = TemporaryFile::where('folder', $folder)->first();

            if ($temp && File::isDirectory(storage_path('app/temporary/tmp/') . $temp->folder)) {
                File::deleteDirectory(storage_path('app/temporary/tmp/') . $temp->folder);
            }

            if ($temp) {
                $temp->delete();
            }

            Log::info("Temporary file deleted", ['folder' => $folder]);

        } catch (\Exception $e) {
            Log::error("Error deleting temporary file {$folder}: " . $e->getMessage());
        }
    }

    public function cleanupExpiredFiles($hours = 24)
    {
        try {
            $expiredFiles = TemporaryFile::where('created_at', '<', now()->subHours($hours))->get();
            
            foreach ($expiredFiles as $tempFile) {
                $this->deleteFile($tempFile->folder);
            }

            Log::info("Cleaned up expired temporary files", ['count' => $expiredFiles->count()]);

        } catch (\Exception $e) {
            Log::error("Error cleaning up expired temporary files: " . $e->getMessage());
        }
    }
}