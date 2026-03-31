<?php

if (!function_exists('processTemporaryFile')) {
    // Helper function to process temporary files
    function processTemporaryFile($tempFile, $destinationFolder) {
        if (is_string($tempFile)) {
            // If it's a folder name from FilePond
            $tempFileModel = \App\Models\TemporaryFile::where('folder', $tempFile)->first();
            if ($tempFileModel) {
                   $tempPath = 'temporary/tmp/' . $tempFile . '/' . $tempFileModel->file;
            $newPath = $destinationFolder . '/' . time() . '_' . $tempFileModel->file;
            
            if (Storage::disk('local')->exists($tempPath)) {
                // Copy file to permanent location
                Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                
                // Clean up temporary file
                Storage::disk('local')->deleteDirectory('temporary/tmp/' . $tempFile);
                $tempFileModel->delete();
                
                return $newPath;
            }
        }
    }
        
    }
    
}
