<?php

namespace Database\Seeders;

use App\Models\Cemetery;
use App\Models\CemeteryArea;
use App\Models\Burial;
use App\Models\Concession;
use App\Models\Grave;
use Illuminate\Database\Seeder;

class CemeterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cemetery 1: Agenzia piccola
        $cemetery1 = Cemetery::create([
            'agency_id' => 1,
            'branch_id' => 1,
            'name' => 'Cimitero Comunale Milano Sud',
            'address' => 'Via del Riposo 45',
            'city' => 'Milano',
            'postal_code' => '20100',
            'total_graves' => 200,
            'is_active' => true,
        ]);

        // Area 1: Loculi
        $area1 = CemeteryArea::create([
            'cemetery_id' => $cemetery1->id,
            'name' => 'Sezione A - Colombario',
            'area_type' => 'wall',
            'total_graves' => 100,
            'floor_level' => 0,
        ]);

        // Create some graves in area 1
        for ($i = 1; $i <= 10; $i++) {
            Grave::create([
                'cemetery_area_id' => $area1->id,
                'grave_number' => 'A-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'grave_type' => 'loculo',
                'status' => $i <= 5 ? 'occupied' : 'available',
                'row' => ceil($i / 2),
                'column' => ($i % 2) + 1,
                'max_burials' => 1,
                'current_burials' => $i <= 5 ? 1 : 0,
            ]);
        }

        // Cemetery 2: Agenzia grande
        $cemetery2 = Cemetery::create([
            'agency_id' => 2,
            'branch_id' => 4,
            'name' => 'Cimitero Monumentale di Milano',
            'address' => 'Piazzale Cimitero Monumentale 1',
            'city' => 'Milano',
            'postal_code' => '20154',
            'total_graves' => 1000,
            'is_active' => true,
        ]);

        // Area 2.1: Colombario
        $area2_1 = CemeteryArea::create([
            'cemetery_id' => $cemetery2->id,
            'name' => 'Sezione B - Colombario Nord',
            'area_type' => 'wall',
            'total_graves' => 500,
            'floor_level' => 0,
        ]);

        // Area 2.2: Tombe di famiglia
        $area2_2 = CemeteryArea::create([
            'cemetery_id' => $cemetery2->id,
            'name' => 'Sezione C - Tombe di Famiglia',
            'area_type' => 'ground',
            'total_graves' => 300,
            'floor_level' => 0,
        ]);

        // Create graves with burials and concessions
        // Grave 1: Occupied with active concession
        $grave1 = Grave::create([
            'cemetery_area_id' => $area2_1->id,
            'grave_number' => 'B-001',
            'grave_type' => 'loculo',
            'status' => 'occupied',
            'row' => 1,
            'column' => 1,
            'max_burials' => 1,
            'current_burials' => 1,
        ]);

        $burial1 = Burial::create([
            'grave_id' => $grave1->id,
            'deceased_name' => 'Mario Bianchi',
            'death_date' => now()->subYears(5),
            'burial_date' => now()->subYears(5)->addDays(3),
            'burial_type' => 'entombment',
        ]);

        Concession::create([
            'grave_id' => $grave1->id,
            'burial_id' => $burial1->id,
            'concessionaire_name' => 'Famiglia Bianchi',
            'concessionaire_phone' => '+39 333 1234567',
            'concession_date' => now()->subYears(5),
            'expiry_date' => now()->addYears(15), // 20 years total, 15 remaining
            'duration_years' => 20,
            'status' => 'active',
            'fee_paid' => 2000.00,
            'fee_paid_date' => now()->subYears(5),
        ]);

        // Grave 2: Occupied with expiring concession (< 90 days)
        $grave2 = Grave::create([
            'cemetery_area_id' => $area2_1->id,
            'grave_number' => 'B-002',
            'grave_type' => 'loculo',
            'status' => 'occupied',
            'row' => 1,
            'column' => 2,
            'max_burials' => 1,
            'current_burials' => 1,
        ]);

        $burial2 = Burial::create([
            'grave_id' => $grave2->id,
            'deceased_name' => 'Giuseppe Rossi',
            'death_date' => now()->subYears(20),
            'burial_date' => now()->subYears(20)->addDays(2),
            'burial_type' => 'entombment',
        ]);

        Concession::create([
            'grave_id' => $grave2->id,
            'burial_id' => $burial2->id,
            'concessionaire_name' => 'Famiglia Rossi',
            'concessionaire_phone' => '+39 333 9876543',
            'concession_date' => now()->subYears(20),
            'expiry_date' => now()->addDays(60), // Expiring in 60 days!
            'duration_years' => 20,
            'status' => 'expiring',
            'fee_paid' => 1500.00,
            'fee_paid_date' => now()->subYears(20),
        ]);

        // Grave 3: Occupied with expired concession
        $grave3 = Grave::create([
            'cemetery_area_id' => $area2_1->id,
            'grave_number' => 'B-003',
            'grave_type' => 'loculo',
            'status' => 'occupied',
            'row' => 1,
            'column' => 3,
            'max_burials' => 1,
            'current_burials' => 1,
        ]);

        $burial3 = Burial::create([
            'grave_id' => $grave3->id,
            'deceased_name' => 'Anna Verdi',
            'death_date' => now()->subYears(25),
            'burial_date' => now()->subYears(25)->addDays(4),
            'burial_type' => 'entombment',
        ]);

        Concession::create([
            'grave_id' => $grave3->id,
            'burial_id' => $burial3->id,
            'concessionaire_name' => 'Famiglia Verdi',
            'concessionaire_phone' => '+39 340 1122334',
            'concession_date' => now()->subYears(25),
            'expiry_date' => now()->subDays(30), // Expired 30 days ago!
            'duration_years' => 25,
            'status' => 'expired',
            'fee_paid' => 1200.00,
            'fee_paid_date' => now()->subYears(25),
        ]);

        // Create available graves
        for ($i = 4; $i <= 20; $i++) {
            Grave::create([
                'cemetery_area_id' => $area2_1->id,
                'grave_number' => 'B-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'grave_type' => 'loculo',
                'status' => 'available',
                'row' => ceil($i / 10),
                'column' => ($i % 10) + 1,
                'max_burials' => 1,
                'current_burials' => 0,
            ]);
        }

        $this->command->info('✅ Seeded cemeteries');
        $this->command->info('   - Cemetery 1 (Agency 1): ' . $cemetery1->name);
        $this->command->info('   - Cemetery 2 (Agency 2): ' . $cemetery2->name);
        $this->command->info('   - Total graves: ' . Grave::count());
        $this->command->info('   - Total burials: ' . Burial::count());
        $this->command->info('   - Total concessions: ' . Concession::count());
        $this->command->info('');
        $this->command->info('💡 Concession status:');
        $this->command->info('   - Active: ' . Concession::active()->count());
        $this->command->info('   - Expiring: ' . Concession::expiring()->count());
        $this->command->info('   - Expired: ' . Concession::expired()->count());
    }
}
