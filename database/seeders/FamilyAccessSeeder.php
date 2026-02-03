<?php

namespace Database\Seeders;

use App\Models\Funeral;
use App\Models\FamilyAccessToken;
use App\Models\FamilyAccessLog;
use Illuminate\Database\Seeder;

class FamilyAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Token 1: Full access, valid for 30 days
        $funeral1 = Funeral::where('agency_id', 1)->first();

        if ($funeral1) {
            $token1 = FamilyAccessToken::create([
                'funeral_id' => $funeral1->id,
                'access_level' => 'full',
                'granted_to_name' => 'Maria Verdi',
                'granted_to_email' => 'maria.verdi@example.com',
                'granted_to_phone' => '+39 333 1111111',
                'expires_at' => now()->addDays(30),
                'created_by_user_id' => 1,
            ]);

            // Simulate some access
            $token1->recordAccess('funeral', 'view');
            $token1->recordAccess('timeline', 'view');
            $token1->recordAccess('documents', 'view');
        }

        // Token 2: Limited access, expiring soon (5 days)
        $funeral2 = Funeral::where('agency_id', 2)->first();

        if ($funeral2) {
            $token2 = FamilyAccessToken::create([
                'funeral_id' => $funeral2->id,
                'access_level' => 'limited',
                'granted_to_name' => 'Giovanni Rossi',
                'granted_to_email' => 'giovanni.rossi@example.com',
                'expires_at' => now()->addDays(5),
                'created_by_user_id' => 4,
            ]);

            // Simulate access
            $token2->recordAccess('funeral', 'view');
        }

        // Token 3: Documents only, expired
        $funeral3 = Funeral::where('agency_id', 2)->skip(1)->first();

        if ($funeral3) {
            $token3 = FamilyAccessToken::create([
                'funeral_id' => $funeral3->id,
                'access_level' => 'documents_only',
                'granted_to_name' => 'Anna Bianchi',
                'granted_to_email' => 'anna.bianchi@example.com',
                'expires_at' => now()->subDays(10), // Already expired
                'created_by_user_id' => 4,
            ]);

            // Simulate old access
            FamilyAccessLog::create([
                'family_access_token_id' => $token3->id,
                'accessed_at' => now()->subDays(15),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0',
                'accessed_resource' => 'documents',
                'action' => 'view',
            ]);
        }

        // Token 4: Revoked token
        $funeral4 = Funeral::where('agency_id', 1)->skip(1)->first();

        if ($funeral4) {
            $token4 = FamilyAccessToken::create([
                'funeral_id' => $funeral4->id,
                'access_level' => 'full',
                'granted_to_name' => 'Luigi Neri',
                'granted_to_email' => 'luigi.neri@example.com',
                'expires_at' => now()->addDays(30),
                'created_by_user_id' => 1,
            ]);

            // Revoke it
            $token4->revoke(1, 'Richiesta dalla famiglia');
        }

        // Token 5: Never expires (permanent family record access)
        if ($funeral1) {
            $token5 = FamilyAccessToken::create([
                'funeral_id' => $funeral1->id,
                'access_level' => 'cemetery_only',
                'granted_to_name' => 'Famiglia Verdi',
                'granted_to_email' => 'famiglia.verdi@example.com',
                'expires_at' => null, // Never expires
                'created_by_user_id' => 1,
                'notes' => 'Accesso permanente per info cimitero',
            ]);
        }

        $this->command->info('✅ Seeded family access tokens');
        $this->command->info('   - Total tokens: ' . FamilyAccessToken::count());
        $this->command->info('   - Valid tokens: ' . FamilyAccessToken::valid()->count());
        $this->command->info('   - Expired tokens: ' . FamilyAccessToken::expired()->count());
        $this->command->info('   - Access logs: ' . FamilyAccessLog::count());
    }
}
