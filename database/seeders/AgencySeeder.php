<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Agenzia 1: Piccola agenzia familiare
        $agency1 = Agency::create([
            'name' => 'Onoranze Funebri Rossi',
            'vat_number' => 'IT12345678901',
            'email' => 'info@onoranzefunebrirossi.it',
            'phone' => '+39 02 12345678',
            'address' => 'Via Roma 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'is_active' => true,
        ]);

        // Sede principale
        $branch1 = Branch::create([
            'agency_id' => $agency1->id,
            'name' => 'Sede Centrale Milano',
            'address' => 'Via Roma 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'phone' => '+39 02 12345678',
            'is_main' => true,
        ]);

        // Owner
        User::create([
            'agency_id' => $agency1->id,
            'branch_id' => null, // Può accedere a tutte le sedi
            'name' => 'Mario Rossi',
            'email' => 'mario.rossi@onoranzefunebrirossi.it',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Operatore
        User::create([
            'agency_id' => $agency1->id,
            'branch_id' => $branch1->id,
            'name' => 'Giulia Bianchi',
            'email' => 'giulia.bianchi@onoranzefunebrirossi.it',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ========================================
        // Agenzia 2: Grande agenzia multi-sede
        // ========================================
        $agency2 = Agency::create([
            'name' => 'Gruppo Funerario Lombardo',
            'vat_number' => 'IT98765432109',
            'email' => 'info@gruppofunerariolombardo.it',
            'phone' => '+39 02 98765432',
            'address' => 'Corso Italia 456',
            'city' => 'Milano',
            'postal_code' => '20122',
            'is_active' => true,
        ]);

        // Sede 1: Milano Centro
        $branch2_1 = Branch::create([
            'agency_id' => $agency2->id,
            'name' => 'Sede Milano Centro',
            'address' => 'Corso Italia 456',
            'city' => 'Milano',
            'postal_code' => '20122',
            'phone' => '+39 02 98765432',
            'is_main' => true,
        ]);

        // Sede 2: Monza
        $branch2_2 = Branch::create([
            'agency_id' => $agency2->id,
            'name' => 'Sede Monza',
            'address' => 'Via Libertà 78',
            'city' => 'Monza',
            'postal_code' => '20900',
            'phone' => '+39 039 1234567',
            'is_main' => false,
        ]);

        // Sede 3: Bergamo
        $branch2_3 = Branch::create([
            'agency_id' => $agency2->id,
            'name' => 'Sede Bergamo',
            'address' => 'Viale Vittoria 90',
            'city' => 'Bergamo',
            'postal_code' => '24100',
            'phone' => '+39 035 9876543',
            'is_main' => false,
        ]);

        // Owner del gruppo
        User::create([
            'agency_id' => $agency2->id,
            'branch_id' => null, // Accesso a tutte le sedi
            'name' => 'Carlo Verdi',
            'email' => 'carlo.verdi@gruppofunerariolombardo.it',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Branch Manager Milano
        User::create([
            'agency_id' => $agency2->id,
            'branch_id' => $branch2_1->id,
            'name' => 'Laura Neri',
            'email' => 'laura.neri@gruppofunerariolombardo.it',
            'password' => Hash::make('password'),
            'role' => 'branch_manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Branch Manager Monza
        User::create([
            'agency_id' => $agency2->id,
            'branch_id' => $branch2_2->id,
            'name' => 'Marco Ferrari',
            'email' => 'marco.ferrari@gruppofunerariolombardo.it',
            'password' => Hash::make('password'),
            'role' => 'branch_manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Operatore Bergamo
        User::create([
            'agency_id' => $agency2->id,
            'branch_id' => $branch2_3->id,
            'name' => 'Anna Conti',
            'email' => 'anna.conti@gruppofunerariolombardo.it',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Staff generico (può essere assegnato a qualsiasi sede)
        User::create([
            'agency_id' => $agency2->id,
            'branch_id' => null,
            'name' => 'Paolo Colombo',
            'email' => 'paolo.colombo@gruppofunerariolombardo.it',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Seeded 2 agencies, 4 branches, and 8 users');
        $this->command->info('');
        $this->command->info('🔑 Login credentials (password: "password" for all):');
        $this->command->info('');
        $this->command->info('Agency 1 (Piccola):');
        $this->command->info('  - mario.rossi@onoranzefunebrirossi.it (owner)');
        $this->command->info('  - giulia.bianchi@onoranzefunebrirossi.it (operator)');
        $this->command->info('');
        $this->command->info('Agency 2 (Grande):');
        $this->command->info('  - carlo.verdi@gruppofunerariolombardo.it (owner)');
        $this->command->info('  - laura.neri@gruppofunerariolombardo.it (branch_manager - Milano)');
        $this->command->info('  - marco.ferrari@gruppofunerariolombardo.it (branch_manager - Monza)');
        $this->command->info('  - anna.conti@gruppofunerariolombardo.it (operator - Bergamo)');
        $this->command->info('  - paolo.colombo@gruppofunerariolombardo.it (staff - all branches)');
    }
}
