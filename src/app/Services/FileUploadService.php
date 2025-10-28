<?php

namespace App\Services;

use App\Services\Contracts\FileUploadServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Exception;

/**
 * FileUploadService
 * 
 * Centralized file upload service with image optimization,
 * thumbnail generation, and file management capabilities.
 */
class FileUploadService implements FileUploadServiceInterface
{
    /**
     * Default configuration
     */
    protected array $config = [
        'max_file_size' => 10240, // 10MB in KB
        'max_image_width' => 1920,
        'max_image_height' => 1080,
        'thumbnail_size' => 300,
        'image_quality' => 85,
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    ];

    /**
     * Upload and optimize an image file
     *
     * @param UploadedFile $file
     * @param string $directory Directory path relative to storage/app/public
     * @param array $options Override default config options
     * @return array ['path' => string, 'thumbnail' => string|null, 'url' => string]
     * @throws Exception
     */
    public function uploadImage(UploadedFile $file, string $directory, array $options = []): array
    {
        // Merge options with default config
        $config = array_merge($this->config, $options);

        // Validate image
        $this->validateImage($file, $config);

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $path = $directory . '/' . $filename;

        // Store original file first
        $fullPath = $file->storeAs($directory, $filename, 'public');

        // Optimize image if requested (default true)
        if ($config['optimize'] ?? true) {
            $this->optimizeImage(
                $fullPath,
                $config['max_image_width'],
                $config['max_image_height'],
                $config['image_quality']
            );
        }

        // Generate thumbnail if requested
        $thumbnailPath = null;
        if ($config['generate_thumbnail'] ?? false) {
            $thumbnailPath = $this->generateThumbnail(
                $fullPath,
                $config['thumbnail_size']
            );
        }

        return [
            'path' => $fullPath,
            'thumbnail' => $thumbnailPath,
            'url' => Storage::disk('public')->url($fullPath),
            'thumbnail_url' => $thumbnailPath ? Storage::disk('public')->url($thumbnailPath) : null,
            'filename' => $filename,
            'size' => Storage::disk('public')->size($fullPath),
        ];
    }

    /**
     * Upload a general file (documents, etc)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $allowedTypes
     * @return array ['path' => string, 'url' => string]
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $directory, array $allowedTypes = []): array
    {
        // Use default allowed types if not specified
        if (empty($allowedTypes)) {
            $allowedTypes = $this->config['allowed_document_types'];
        }

        // Validate file
        $this->validateFile($file, $allowedTypes);

        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Store file
        $path = $file->storeAs($directory, $filename, 'public');

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete a file from storage
     *
     * @param string|null $path Path relative to storage/app/public
     * @return bool
     */
    public function deleteFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Delete multiple files
     *
     * @param array $paths
     * @return int Number of files deleted
     */
    public function deleteMultiple(array $paths): int
    {
        $deleted = 0;

        foreach ($paths as $path) {
            if ($this->deleteFile($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Optimize an image (resize and compress)
     *
     * @param string $path Path relative to storage/app/public
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
    ): bool {
        try {
            $fullPath = Storage::disk('public')->path($path);

            // Load image
            $image = Image::read($fullPath);

            // Get original dimensions
            $width = $image->width();
            $height = $image->height();

            // Calculate new dimensions if image is larger than max
            if ($width > $maxWidth || $height > $maxHeight) {
                $image->scale(width: $maxWidth, height: $maxHeight);
            }

            // Save optimized image
            $image->save($fullPath, quality: $quality);

            return true;
        } catch (Exception $e) {
            // Log error but don't fail the upload
            logger()->error('Failed to optimize image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a thumbnail from an image
     *
     * @param string $path Original image path relative to storage/app/public
     * @param int $size Thumbnail size (square)
     * @return string|null Thumbnail path or null if failed
     */
    public function generateThumbnail(string $path, int $size = 300): ?string
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            
            // Generate thumbnail filename
            $pathInfo = pathinfo($path);
            $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $thumbnailFilename;
            $thumbnailFullPath = Storage::disk('public')->path($thumbnailPath);

            // Create thumbnails directory if not exists
            $thumbnailDir = dirname($thumbnailFullPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Load and resize image
            $image = Image::read($fullPath);
            $image->cover($size, $size); // Crop to square
            $image->save($thumbnailFullPath, quality: 80);

            return $thumbnailPath;
        } catch (Exception $e) {
            logger()->error('Failed to generate thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @param array $config
     * @throws Exception
     */
    protected function validateImage(UploadedFile $file, array $config): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('File upload gagal: ' . $file->getErrorMessage());
        }

        // Check file size (convert to KB)
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $config['max_file_size']) {
            throw new Exception(
                'Ukuran file terlalu besar. Maksimal ' . 
                round($config['max_file_size'] / 1024, 2) . 'MB'
            );
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $config['allowed_image_types'])) {
            throw new Exception(
                'Tipe file tidak diizinkan. Hanya: ' . 
                implode(', ', $config['allowed_image_types'])
            );
        }

        // Check if file is actually an image
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            throw new Exception('File yang diupload bukan gambar yang valid');
        }
    }

    /**
     * Validate general file
     *
     * @param UploadedFile $file
     * @param array $allowedTypes
     * @throws Exception
     */
    protected function validateFile(UploadedFile $file, array $allowedTypes): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('File upload gagal: ' . $file->getErrorMessage());
        }

        // Check file size
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $this->config['max_file_size']) {
            throw new Exception(
                'Ukuran file terlalu besar. Maksimal ' . 
                round($this->config['max_file_size'] / 1024, 2) . 'MB'
            );
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception(
                'Tipe file tidak diizinkan. Hanya: ' . 
                implode(', ', $allowedTypes)
            );
        }
    }

    /**
     * Generate unique filename with timestamp and random string
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        
        return $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Get file URL from path
     *
     * @param string|null $path
     * @return string|null
     */
    public function getUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get file size in bytes
     *
     * @param string $path
     * @return int
     */
    public function getSize(string $path): int
    {
        return Storage::disk('public')->size($path);
    }
}
