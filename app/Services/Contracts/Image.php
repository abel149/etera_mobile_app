<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface Image
{
    /**
     * Upload an image.
     *
     * @param mixed $image The image data or file path.
     * @return string The URL or identifier of the uploaded image.
     */
    public function upload(Request $request, $houseId);

    /**
     * Get the URL of an uploaded image.
     *
     * @param string $imageId The identifier of the image.
     * @return string|null The URL of the image or null if not found.
     */
    public function getUrl($imageId);

    /**
     * Delete an uploaded image.
     *
     * @param string $imageId The identifier of the image to delete.
     * @return bool True if the image was successfully deleted, false otherwise.
     */
    public function delete($imageId);

    /**
     * Copy an image.
     *
     * @param string $sourceImageId The identifier of the source image.
     * @param string $destinationPath The path or identifier where the image should be copied.
     * @return bool True if the image was successfully copied, false otherwise.
     */
    public function copyImages(Request $request);
}
