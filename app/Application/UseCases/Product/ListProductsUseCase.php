<?php

namespace App\Application\UseCases\Product;

use App\Application\DTOs\Requests\Product\ListProductsRequest;
use App\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    public function execute(ListProductsRequest $request): Collection
    {
        return $this->productRepository->getActiveProducts(
            $request->tenantId,
            $request->category
        );
    }
}
