<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use App\Models\Image as ImageModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImageService
{
    public function upload(Request $request, $proformaId)
    {
        try {
            Log::info('ImageService: upload started', [
                'proforma_id' => $proformaId,
            ]);

            $images = $this->copyImages($request);

            if (empty($images)) {
                return;
            }

            foreach ($images as $image) {
                ImageModel::create([
                    'path' => $image,
                    'proforma_id' => $proformaId,
                ]);
            }

            Log::info('ImageService: upload completed', [
                'proforma_id' => $proformaId,
                'count' => count($images),
            ]);

        } catch (\Exception $e) {
            Log::error('ImageService: upload failed', [
                'proforma_id' => $proformaId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getUrl($imageId)
    {
        $image = ImageModel::find($imageId);
        if (!$image) {
            return null;
        }
        
        return asset($image->path);
    }

    public function delete($imageId)
    {
        try {
            $image = ImageModel::findOrFail($imageId);

            $imagePath = public_path($image->path);

            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }

            $image->delete();

            Log::info("Image deleted successfully", ['image_id' => $imageId]);

            return response()->json([
                'status' => true,
                'message' => 'The image has been deleted',
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting image {$imageId}: " . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Error deleting image: ' . $e->getMessage(),
            ], 500);
        }
    }

public function copyImages(Request $request)
{
    $imagePaths = [];
    $rawImages = $request->image ?? [];

    $folders = [];

    // FIX: decode each json string
    foreach ($rawImages as $item) {
        $json = json_decode($item, true);

        if ($json && isset($json['folders'][0])) {
            $folders[] = $json['folders'][0];
        }
    }

    Log::info("Image Loaded successfully", ['folders' => $folders]);

    if (empty($folders)) {
        return null;
    }

    foreach ($folders as $folder) {
        $temp = TemporaryFile::where('folder', $folder)->first();

        if (!$temp) continue;

        $fileName = time() . '_' . uniqid() . '_' . $temp->file;
        $sourcePath = storage_path("app/temporary/tmp/{$temp->folder}/{$temp->file}");
        $destinationPath = public_path("uploads/{$fileName}");

        if (!File::isDirectory(public_path('uploads'))) {
            File::makeDirectory(public_path('uploads'), 0755, true);
        }

        File::copy($sourcePath, $destinationPath);
        $imagePaths[] = "uploads/{$fileName}";

        // Clean temp
        File::deleteDirectory(storage_path("app/temporary/tmp/{$temp->folder}"));
        $temp->delete();
    }

    return $imagePaths;
}

}