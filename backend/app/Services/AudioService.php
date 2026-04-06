<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use App\Models\Audio as AudioModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AudioService
{
    public function upload(Request $request, $proformaId)
    {
        try {
            $audios = $this->copyAudios($request);

            if (empty($audios)) {
                return;
            }

            foreach ($audios as $audio) {
                AudioModel::create([
                    'path' => $audio,
                    'proforma_id' => $proformaId,
                ]);
            }

            Log::info("Audio files uploaded successfully for proforma {$proformaId}", ['count' => count($audios)]);

        } catch (\Exception $e) {
            Log::error("Error uploading audio files for proforma {$proformaId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUrl($audioId)
    {
        $audio = AudioModel::find($audioId);
        if (!$audio) {
            return null;
        }
        
        return asset($audio->path);
    }

    public function delete($audioId)
    {
        try {
            $audio = AudioModel::findOrFail($audioId);

            $audioPath = public_path($audio->path);

            if (File::exists($audioPath)) {
                File::delete($audioPath);
            }

            $audio->delete();

            Log::info("Audio file deleted successfully", ['audio_id' => $audioId]);

            return response()->json([
                'status' => true,
                'message' => 'The audio file has been deleted',
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting audio file {$audioId}: " . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Error deleting audio file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function copyAudios(Request $request)
    {
        $audioPaths = [];
        $audios = json_decode($request->audio, true);

        if (empty($audios)) {
            return null;
        }

        foreach ($audios as $folder) {
            $temp = TemporaryFile::where('folder', $folder)->first();

            if (!$temp) {
                continue;
            }

            $fileName = time() . '_' . uniqid() . '_' . $temp->file;
            $sourcePath = storage_path("app/temporary/tmp/{$temp->folder}/{$temp->file}");
            $destinationPath = public_path("uploads/{$fileName}");

            // Ensure uploads directory exists
            if (!File::isDirectory(public_path('uploads'))) {
                File::makeDirectory(public_path('uploads'), 0755, true);
            }

            File::copy($sourcePath, $destinationPath);

            $audioPaths[] = "uploads/{$fileName}";

            // Clean up temporary files
            File::deleteDirectory(storage_path("app/temporary/tmp/{$temp->folder}"));
            $temp->delete();
        }
        
        return $audioPaths;
    }
}