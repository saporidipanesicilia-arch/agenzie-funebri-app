<?php

namespace App\Application\DTOs\Requests\Product;

readonly class ListProductsRequest
{
    public function __construct(
        public int $tenantId,
        public ?string $category = null,
    ) {
    }
}
