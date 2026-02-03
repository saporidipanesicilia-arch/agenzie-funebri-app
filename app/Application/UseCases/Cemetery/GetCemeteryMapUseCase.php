<?php

namespace App\Application\UseCases\Cemetery;

use App\Models\Cemetery;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetCemeteryMapUseCase
{
    /**
     * Get cemetery details including map.
     * 
     * @param int $cemeteryId
     * @return array
     */
    public function execute(int $cemeteryId): array
    {
        $cemetery = Cemetery::with(['areas', 'maps'])->findOrFail($cemeteryId);

        return [
            'id' => $cemetery->id,
            'name' => $cemetery->name,
            'address' => $cemetery->address,
            'city' => $cemetery->city,
            'areas' => $cemetery->areas,
            'maps' => $cemetery->maps->map(function ($map) {
                return [
                    'name' => $map->name,
                    'url' => $map->file_url, // Assuming Accessor exists
                    'description' => $map->description
                ];
            })
        ];
    }
}
