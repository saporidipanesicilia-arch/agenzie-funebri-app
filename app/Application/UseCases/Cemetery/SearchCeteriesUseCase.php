<?php

namespace App\Application\UseCases\Cemetery;

use App\Models\Cemetery;
use Illuminate\Database\Eloquent\Collection;

class SearchCeteriesUseCase
{
    /**
     * Search cemeteries by name or city.
     * 
     * @param string|null $query Search string
     * @return Collection
     */
    public function execute(?string $query): Collection
    {
        if (empty($query)) {
            return Cemetery::limit(20)->get();
        }

        return Cemetery::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->limit(50)
            ->get();
    }
}
