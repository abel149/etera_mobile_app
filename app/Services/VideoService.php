<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use App\Models\Video as VideoModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class VideoService
{
    public function upload(Request $request, $proformaId)
    {
        try {
            $videos = $this->copyVideos($request);

            if (empty($videos)) {
                return;
            }

            foreach ($videos as $video) {
                VideoModel::create([
                    'path' => $video,
                    'proforma_id' => $proformaId,
                ]);
            }

            Log::info("Videos uploaded successfully for proforma {$proformaId}", ['count' => count($videos)]);

        } catch (\Exception $e) {
            Log::error("Error uploading videos for proforma {$proformaId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUrl($videoId)
    {
        $video = VideoModel::find($videoId);
        if (!$video) {
            return null;
        }
        
        return asset($video->path);
    }

    public function delete($videoId)
    {
        try {
            $video = VideoModel::findOrFail($videoId);

            $videoPath = public_path($video->path);

            if (File::exists($videoPath)) {
                File::delete($videoPath);
            }

            $video->delete();

            Log::info("Video deleted successfully", ['video_id' => $videoId]);

            return response()->json([
                'status' => true,
                'message' => 'The video has been deleted',
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting video {$videoId}: " . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Error deleting video: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function copyVideos(Request $request)
    {
        $videoPaths = [];
        $videos = json_decode($request->video, true);
        
        if (empty($videos)) {
            return null;
        }

        foreach ($videos as $folder) {
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

            $videoPaths[] = "uploads/{$fileName}";

            // Clean up temporary files
            File::deleteDirectory(storage_path("app/temporary/tmp/{$temp->folder}"));
            $temp->delete();
        }
        
        return $videoPaths;
    }
}