<?php

namespace App\Application\UseCases\Family;

use App\Models\FamilyAccessToken;
use Illuminate\Validation\ValidationException;

class AuthenticateFamilyMemberUseCase
{
    /**
     * Authenticate via plain token.
     * 
     * @param string $token
     * @return string Plain token if valid
     * @throws ValidationException
     */
    public function execute(string $token): string
    {
        $accessToken = FamilyAccessToken::validateToken($token);

        if (!$accessToken) {
            throw ValidationException::withMessages([
                'token' => ['Token di accesso non valido o scaduto.']
            ]);
        }

        // Record usage
        $accessToken->recordAccess('login_attempt', 'login');

        // Return the plain token to be stored in session request
        return $token;
    }
}
