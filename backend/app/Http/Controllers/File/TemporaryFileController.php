<?php

namespace App\Http\Controllers\File;

use Illuminate\Http\Request;
use App\Services\TemporaryFileService;
use App\Http\Controllers\Controller;

class TemporaryFileController extends Controller
{
    protected $temporaryFileService;

    public function __construct(TemporaryFileService $temporaryFileService)
    {
        $this->temporaryFileService = $temporaryFileService;
    }

    public function store(Request $request, $type)
    {
        $files = $request->file($type);

        if (!is_array($files)) {
            $files = [$files];
        }

        $folders = [];

        foreach ($files as $file) {
            $folders[] = $this->temporaryFileService->storeFile($file, $type);
        }

        return response()->json(['folders' => $folders], 200);
    }

    public function destroy(Request $request)
    {
        $folder = $request->getContent();
        $this->temporaryFileService->deleteFile($folder);

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
