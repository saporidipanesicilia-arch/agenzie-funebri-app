<?php

namespace App\Application\UseCases\Family;

use App\Models\FamilyAccessToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetFamilyDashboardDataUseCase
{
    public function execute(string $token): array
    {
        $accessToken = FamilyAccessToken::validateToken($token);

        if (!$accessToken) {
            throw new ModelNotFoundException("Token invalido");
        }

        $funeral = $accessToken->funeral()->with([
            'deceased',
            'timeline' => function ($q) {
                // Only show steps visible to family (simplified logic for now)
                $q->orderBy('id'); // Assuming timeline steps have order
            },
            'documents' => function ($q) {
                $q->where('is_visible_to_family', true);
            }
        ])->firstOrFail();

        return [
            'funeral' => $funeral,
            'deceased' => $funeral->deceased,
            'timeline' => $funeral->timeline,
            'documents' => $funeral->documents,
            'token_info' => [
                'expires_at' => $accessToken->expires_at,
                'access_level' => $accessToken->access_level
            ]
        ];
    }
}
