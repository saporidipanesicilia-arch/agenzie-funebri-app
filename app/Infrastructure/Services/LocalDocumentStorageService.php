<?php

namespace App\Infrastructure\Services;

use App\Domain\Services\DocumentStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Local implementation of DocumentStorageService using Laravel's Storage facade.
 * 
 * Default disk: 'private' (not accessible via public URL directly).
 */
class LocalDocumentStorageService implements DocumentStorageServiceInterface
{
    protected string $disk = 'local'; // Change to 's3' or other in config

    public function store(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $name = $filename ?? $file->hashName();

        return $file->storeAs($directory, $name, $this->disk);
    }

    public function get(string $path): ?string
    {
        if (!$this->exists($path)) {
            return null;
        }
        return Storage::disk($this->disk)->get($path);
    }

    public function getUrl(string $path): string
    {
        // For local storage, we might need a temporary signed URL or a route that streams the file
        // assuming secure documents. for now returning path for internal use
        // In a real S3 setup: return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes(15));

        return Storage::disk($this->disk)->url($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}
