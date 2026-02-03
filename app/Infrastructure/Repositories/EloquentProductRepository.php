<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function getActiveProducts(int $tenantId, ?string $category = null): Collection
    {
        $query = Product::where('agency_id', $tenantId); // Assuming agency_id is the tenant key

        if ($category) {
            $query->where('category', $category); // Assuming 'category' column exists
        }

        return $query->get();
    }

    public function findById(int $id, int $tenantId)
    {
        return Product::where('agency_id', $tenantId)->findOrFail($id);
    }

    public function findMany(array $ids, int $tenantId): Collection
    {
        return Product::where('agency_id', $tenantId)
            ->whereIn('id', $ids)
            ->get();
    }
}
