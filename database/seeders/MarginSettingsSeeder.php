<?php

namespace Database\Seeders;

use App\Models\MarginSettings;
use Illuminate\Database\Seeder;

class MarginSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Settings per Agenzia 1 (Piccola) - Margini più bassi
        MarginSettings::create([
            'agency_id' => 1,
            'minimum_margin_percentage' => 20.00,  // Target: 20%
            'warning_margin_percentage' => 12.00,   // Warning: sotto 12%
            'critical_margin_percentage' => 5.00,   // Critico: sotto 5%
            'alert_enabled' => true,
            'block_negative_margin' => true,
            'require_approval_for_low_margin' => true,
        ]);

        // Settings per Agenzia 2 (Grande) - Margini più alti
        MarginSettings::create([
            'agency_id' => 2,
            'minimum_margin_percentage' => 30.00,  // Target: 30%
            'warning_margin_percentage' => 20.00,   // Warning: sotto 20%
            'critical_margin_percentage' => 10.00,  // Critico: sotto 10%
            'alert_enabled' => true,
            'block_negative_margin' => true,
            'require_approval_for_low_margin' => true,
        ]);

        $this->command->info('✅ Seeded margin settings');
        $this->command->info('   - Agency 1 (Piccola): 20% minimum margin');
        $this->command->info('   - Agency 2 (Grande): 30% minimum margin');
    }
}
