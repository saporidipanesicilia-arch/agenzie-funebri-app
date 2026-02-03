<?php

namespace App\Domain\Services;

use Illuminate\Http\UploadedFile;

/**
 * Interface for document storage services.
 * 
 * Allows swapping between local storage, S3, Google Drive, etc.
 * without affecting domain logic.
 */
interface DocumentStorageServiceInterface
{
    /**
     * Store a file and return its path/identifier.
     * 
     * @param UploadedFile $file
     * @param string $directory (e.g., 'funerals/123/documents')
     * @param string|null $filename (optional custom filename)
     * @return string The stored file path
     */
    public function store(UploadedFile $file, string $directory, ?string $filename = null): string;

    /**
     * Retrieve a file's content.
     */
    public function get(string $path): ?string;

    /**
     * Get a temporary public URL (for viewing).
     */
    public function getUrl(string $path): string;

    /**
     * Delete a file.
     */
    public function delete(string $path): bool;

    /**
     * Check if file exists.
     */
    public function exists(string $path): bool;
}
