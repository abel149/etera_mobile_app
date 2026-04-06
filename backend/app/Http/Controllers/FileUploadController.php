<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    public function uploadPartsImage(Request $request)
    {
        try {
            Log::info('File upload started', ['files' => $request->all()]);

            // FilePond sends files as 'filepond' field
            if (!$request->hasFile('filepond')) {
                Log::error('No file uploaded');
                return response('No file uploaded', 400);
            }

            $file = $request->file('filepond');
            
            Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]);

            // Validate the file
            $request->validate([
                'filepond' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240']
            ]);

            // Create temp directory if it doesn't exist
            $tempPath = 'temp/parts_images';
            if (!Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->makeDirectory($tempPath);
            }

            $filename = 'temp_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store in temporary directory
            $path = $file->storeAs($tempPath, $filename, 'public');

            Log::info('File stored successfully', ['path' => $path]);

            // Return the file path for FilePond
            return response($path, 200, [
                'Content-Type' => 'text/plain',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in file upload', ['errors' => $e->errors()]);
            return response('Validation failed: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function deleteUpload(Request $request)
    {
        try {
            $filePath = $request->getContent();
            
            Log::info('Delete request', ['file_path' => $filePath]);

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                Log::info('File deleted successfully', ['path' => $filePath]);
                return response('File deleted', 200);
            }
            
            Log::warning('File not found for deletion', ['path' => $filePath]);
            return response('File not found', 404);

        } catch (\Exception $e) {
            Log::error('File deletion error: ' . $e->getMessage());
            return response('Deletion failed', 500);
        }
    }
}