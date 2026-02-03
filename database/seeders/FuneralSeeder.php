<?php

namespace Database\Seeders;

use App\Models\Deceased;
use App\Models\Funeral;
use App\Models\Relative;
use Illuminate\Database\Seeder;

class FuneralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Funerale 1: Agenzia piccola, inumazione
        $funeral1 = Funeral::create([
            'agency_id' => 1,
            'branch_id' => 1, // Sede Centrale Milano
            'service_type' => 'burial',
            'status' => 'active',
            'ceremony_date' => now()->addDays(2)->setTime(10, 0),
            'ceremony_location' => 'Chiesa di San Giuseppe, Milano',
            'start_date' => now()->subDays(1),
            'notes' => 'Famiglia richiede fiori bianchi',
        ]);

        Deceased::create([
            'funeral_id' => $funeral1->id,
            'first_name' => 'Giuseppe',
            'last_name' => 'Verdi',
            'birth_date' => '1945-03-15',
            'death_date' => now()->subDays(1)->format('Y-m-d'),
            'place_of_birth' => 'Milano',
            'place_of_death' => 'Milano',
            'tax_code' => 'VRDGPP45C15F205K',
        ]);

        Relative::create([
            'funeral_id' => $funeral1->id,
            'name' => 'Maria Verdi',
            'relation_type' => 'Coniuge',
            'phone' => '+39 333 1234567',
            'email' => 'maria.verdi@email.it',
            'is_primary_contact' => true,
        ]);

        Relative::create([
            'funeral_id' => $funeral1->id,
            'name' => 'Luca Verdi',
            'relation_type' => 'Figlio',
            'phone' => '+39 333 9876543',
            'email' => 'luca.verdi@email.it',
            'is_primary_contact' => false,
        ]);

        // Funerale 2: Agenzia grande, cremazione
        $funeral2 = Funeral::create([
            'agency_id' => 2,
            'branch_id' => 4, // Sede Milano Centro
            'service_type' => 'cremation',
            'status' => 'active',
            'ceremony_date' => now()->addDays(1)->setTime(15, 0),
            'ceremony_location' => 'Tempio Crematorio Milano',
            'start_date' => now(),
            'notes' => 'Preferenza per cerimonia laica',
        ]);

        Deceased::create([
            'funeral_id' => $funeral2->id,
            'first_name' => 'Anna',
            'last_name' => 'Colombo',
            'birth_date' => '1960-07-22',
            'death_date' => now()->format('Y-m-d'),
            'place_of_birth' => 'Monza',
            'place_of_death' => 'Milano',
            'tax_code' => 'CLMNNA60L62F704Y',
        ]);

        Relative::create([
            'funeral_id' => $funeral2->id,
            'name' => 'Marco Colombo',
            'relation_type' => 'Fratello',
            'phone' => '+39 340 1122334',
            'email' => 'marco.colombo@email.it',
            'is_primary_contact' => true,
        ]);

        // Funerale 3: Agenzia grande, tumulazione (completato)
        $funeral3 = Funeral::create([
            'agency_id' => 2,
            'branch_id' => 6, // Sede Bergamo
            'service_type' => 'entombment',
            'status' => 'completed',
            'ceremony_date' => now()->subDays(5)->setTime(11, 0),
            'ceremony_location' => 'Cattedrale di Bergamo',
            'start_date' => now()->subDays(7),
            'end_date' => now()->subDays(5),
        ]);

        Deceased::create([
            'funeral_id' => $funeral3->id,
            'first_name' => 'Pietro',
            'last_name' => 'Manzoni',
            'birth_date' => '1938-11-03',
            'death_date' => now()->subDays(7)->format('Y-m-d'),
            'place_of_birth' => 'Bergamo',
            'place_of_death' => 'Bergamo',
            'tax_code' => 'MNZPTR38S03A794M',
        ]);

        Relative::create([
            'funeral_id' => $funeral3->id,
            'name' => 'Silvia Manzoni',
            'relation_type' => 'Figlia',
            'phone' => '+39 335 4455667',
            'email' => 'silvia.manzoni@email.it',
            'is_primary_contact' => true,
        ]);

        $this->command->info('✅ Seeded 3 funerals with deceased and relatives');
        $this->command->info('   - Funeral 1: Giuseppe Verdi (Agency 1, active, burial)');
        $this->command->info('   - Funeral 2: Anna Colombo (Agency 2, active, cremation)');
        $this->command->info('   - Funeral 3: Pietro Manzoni (Agency 2, completed, entombment)');
        $this->command->info('');
        $this->command->info('💡 Timeline steps were auto-created for each funeral');
    }
}
