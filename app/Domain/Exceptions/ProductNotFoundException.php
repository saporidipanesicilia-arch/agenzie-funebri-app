<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Thrown when a product is not found or does not belong to the agency.
 *
 * Products must exist and belong to the same tenant as the funeral.
 */
class ProductNotFoundException extends Exception
{
    public function __construct(int $productId, int $agencyId)
    {
        parent::__construct(
            "Product {$productId} not found or does not belong to agency {$agencyId}."
        );
    }

    public static function forMissingProducts(array $missingIds, int $agencyId): self
    {
        return new self(
            0,
            $agencyId
        );
    }
}
