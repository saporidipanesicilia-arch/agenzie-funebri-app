<?php

namespace App\Domain\Services;

/**
 * Interface for OCR (Optical Character Recognition) services.
 * 
 * Future-proofs the application for automated document scanning.
 */
interface OCRServiceInterface
{
    /**
     * Extract text from a document image or PDF.
     * 
     * @param string $filePath Path to the file in storage
     * @return string Extracted text
     */
    public function extractText(string $filePath): string;

    /**
     * Extract structured data for a specific document type.
     * 
     * @param string $filePath
     * @param string $documentType (e.g., 'identity_card', 'death_certificate')
     * @return array Key-value pairs of extracted data
     */
    public function extractData(string $filePath, string $documentType): array;
}
