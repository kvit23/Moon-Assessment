<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageUploadService
{
    /**
     * Upload a product image.
     *
     * @param UploadedFile $file
     * @param string $path
     * @return array
     */
    public function upload(UploadedFile $file, string $path = 'products'): array
    {
        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Upload main image
        $mainPath = $this->storeImage($file, $path, $filename);

        // Create and upload thumbnail
        $thumbnailPath = $this->createThumbnail($file, $path, $filename);

        return [
            'main' => $mainPath,
            'thumbnail' => $thumbnailPath,
            'filename' => $filename,
        ];
    }

    /**
     * Upload multiple images.
     *
     * @param array $files
     * @param string $path
     * @return array
     */
    public function uploadMultiple(array $files, string $path = 'products'): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->upload($file, $path);
                $uploaded[] = $result['main'];
            }
        }

        return $uploaded;
    }

    /**
     * Delete an image.
     */
    public function delete(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * Delete multiple images.
     */
    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    /**
     * Generate a unique filename.
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $timestamp = now()->format('Ymd_His');
        $uuid = Str::uuid();
        $extension = $file->getClientOriginalExtension();

        return "{$timestamp}_{$uuid}.{$extension}";
    }

    /**
     * Store image on disk.
     */
    protected function storeImage(UploadedFile $file, string $path, string $filename): string
    {
        $fullPath = $this->getStoragePath($path, $filename);

        // Optimize and store
        $image = Image::read($file->getRealPath());
        
        // Resize if too large (max 1200px)
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        Storage::disk('public')->put(
            $fullPath,
            $image->toWebp(quality: 85)
        );

        return $fullPath;
    }

    /**
     * Create and store thumbnail.
     */
    protected function createThumbnail(UploadedFile $file, string $path, string $filename): string
    {
        $thumbnailPath = $this->getStoragePath($path, "thumb_{$filename}");

        $image = Image::read($file->getRealPath());
        
        // Create square thumbnail (300x300)
        $image->cover(width: 300, height: 300);

        Storage::disk('public')->put(
            $thumbnailPath,
            $image->toWebp(quality: 80)
        );

        return $thumbnailPath;
    }

    /**
     * Get full storage path.
     */
    protected function getStoragePath(string $path, string $filename): string
    {
        $datePath = now()->format('Y/m');
        return "{$path}/{$datePath}/{$filename}";
    }
}