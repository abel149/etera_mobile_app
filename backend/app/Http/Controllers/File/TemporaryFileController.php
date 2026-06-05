<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Services\TemporaryFileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;

class TemporaryFileController extends Controller
{
    protected $temporaryFileService;

    public function __construct(TemporaryFileService $temporaryFileService)
    {
        $this->temporaryFileService = $temporaryFileService;
    }

    public function store(Request $request, ?string $type = null)
    {
        $resolvedType = $type ?? (string) $request->input('type', 'temp');
        $files = $request->file($resolvedType);

        if (is_null($files)) {
            $files = $request->file('file') ?? $request->file('files');
        }

        if (is_null($files)) {
            $allFiles = $request->allFiles();
            if (count($allFiles) === 1) {
                $files = reset($allFiles);
            }
        }

        if (empty($files)) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded.',
            ], 422);
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        $folders = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $folders[] = $this->temporaryFileService->storeFile($file, $resolvedType);
        }

        if (empty($folders)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid file uploaded.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'folders' => $folders,
        ], 200);
    }

    public function destroy(Request $request)
    {
        $folder = $request->getContent();
        $this->temporaryFileService->deleteFile($folder);

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
