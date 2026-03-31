<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    /**
     * Store a newly created image in storage and database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeImage(Request $request)
    {
        // 1. Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        if ($request->hasFile('image')) {
            // Get the uploaded file
            $image = $request->file('image');

            // Generate a unique filename
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

            // 2. Store the image in storage/app/public/images
            // The 'public' disk is configured to use the local filesystem
            // The path will be 'public/images/unique-filename.jpg'
            $path = $image->storeAs('public/images', $filename);

            // 3. Register the path in the database
            // The path is relative to the storage/app folder
            $imageModel = new Image();
            $imageModel->path = Storage::url($path);
            $imageModel->save();

            return response()->json([
                'message' => 'Image uploaded and path registered successfully!',
                'path' => $imageModel->path
            ]);
        }

        return response()->json(['message' => 'No image file provided.'], 400);
    }
}
