<?php

namespace App\Infrastructure\Services;

use App\Domain\Services\OCRServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder implementation for OCR.
 * 
 * Currently just logs the request and returns mocked data.
 * Ready to be replaced by AWS Textract / Google Vision API implementation.
 */
class PlaceholderOCRService implements OCRServiceInterface
{
    public function extractText(string $filePath): string
    {
        Log::info("OCR Request for file: {$filePath}");

        // Mock response
        return "OCR functionality is not yet implemented. File path: " . $filePath;
    }

    public function extractData(string $filePath, string $documentType): array
    {
        Log::info("OCR Data Extraction for {$documentType} on file: {$filePath}");

        // RETURN MOCKED DATA
        return match ($documentType) {
            'identity_card' => [
                'name' => 'MARIO',
                'surname' => 'ROSSI',
                'document_number' => 'AB12345CD',
            ],
            'death_certificate' => [
                'date_of_death' => '2026-02-01',
                'place_of_death' => 'MILANO',
            ],
            default => [],
        };
    }
}
