<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class TempController extends Controller
{
    public function uploadPartImage(Request $request)
    {
        Log::info('UploadPartImage: started', [
            'user_id' => auth()->id(),
            'type' => 'image',
            'all_files' => array_keys($request->allFiles()),
            'all_inputs' => $request->except(['_token'])
        ]);

        try {
            $allFiles = $request->allFiles();
            $flattenedFiles = $this->flattenFilesArray($allFiles);
            $uploadedPaths = [];

            foreach ($flattenedFiles as $fieldName => $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    // Save temporarily first
                    $path = $file->store('uploads/temp', 'public');
                    $uploadedPaths[] = [
                        'field' => $fieldName,
                        'original_name' => $file->getClientOriginalName(),
                        'temp_path' => $path,
                        'url' => Storage::disk('public')->url($path),
                    ];

                    Log::info('UploadPartImage: file uploaded to temp', [
                        'field' => $fieldName,
                        'path' => $path
                    ]);
                }
            }

            if (count($uploadedPaths) > 0) {
                return response()->json(['success' => true, 'files' => $uploadedPaths]);
            }

            Log::error('UploadPartImage: no valid file found');
            return response()->json(['success' => false, 'message' => 'No valid file uploaded'], 400);

        } catch (\Exception $e) {
            Log::error('UploadPartImage: failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function flattenFilesArray(array $files, string $prefix = '')
    {
        $flat = [];
        foreach ($files as $key => $value) {
            $fieldName = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenFilesArray($value, $fieldName));
            } else {
                $flat[$fieldName] = $value;
            }
        }
        return $flat;
    }

    public function revert(Request $request)
    {
        try {
            $path = trim($request->getContent(), '"');
            Log::info('DeletePartImage: attempting', ['path' => $path]);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('DeletePartImage: success', ['path' => $path]);
                return response()->json(['success' => true]);
            }

            Log::warning('DeletePartImage: file not found', ['path' => $path]);
            return response()->json(['success' => false, 'message' => 'File not found'], 404);

        } catch (\Exception $e) {
            Log::error('DeletePartImage: failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
