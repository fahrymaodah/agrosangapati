<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * FileUploadServiceInterface
 * 
 * Contract for file upload service
 */
interface FileUploadServiceInterface
{
    /**
     * Upload and optimize an image file
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     */
    public function uploadImage(UploadedFile $file, string $directory, array $options = []): array;

    /**
     * Upload a general file
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $allowedTypes
     * @return array
     */
    public function uploadFile(UploadedFile $file, string $directory, array $allowedTypes = []): array;

    /**
     * Delete a file
     *
     * @param string|null $path
     * @return bool
     */
    public function deleteFile(?string $path): bool;

    /**
     * Delete multiple files
     *
     * @param array $paths
     * @return int
     */
    public function deleteMultiple(array $paths): int;

    /**
     * Optimize an image
     *
     * @param string $path
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $quality
     * @return bool
     */
    public function optimizeImage(
        string $path,
        int $maxWidth = 1920,
        int $maxHeight = 1080,
        int $quality = 85
    ): bool;

    /**
     * Generate a thumbnail
     *
     * @param string $path
     * @param int $size
     * @return string|null
     */
    public function generateThumbnail(string $path, int $size = 300): ?string;

    /**
     * Get file URL
     *
     * @param string|null $path
     * @return string|null
     */
    public function getUrl(?string $path): ?string;

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Get file size
     *
     * @param string $path
     * @return int
     */
    public function getSize(string $path): int;
}
