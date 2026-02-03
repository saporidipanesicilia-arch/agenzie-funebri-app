<?php

namespace Database\Seeders;

use App\Models\TimelineStep;
use Illuminate\Database\Seeder;

class TimelineStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template steps per Agenzia 1 (Piccola)
        $agency1Steps = [
            ['name' => 'Ritiro salma', 'description' => 'Ritiro della salma dal luogo del decesso', 'order' => 1, 'is_required' => true, 'estimated_duration_hours' => 2],
            ['name' => 'Vestizione', 'description' => 'Vestizione del defunto', 'order' => 2, 'is_required' => true, 'estimated_duration_hours' => 1],
            ['name' => 'Preparazione documenti', 'description' => 'Raccolta documenti per pratiche amministrative', 'order' => 3, 'is_required' => true, 'estimated_duration_hours' => 4],
            ['name' => 'Affissioni manifesti', 'description' => 'Stampa e affissione manifesti funebri', 'order' => 4, 'is_required' => false, 'estimated_duration_hours' => 2],
            ['name' => 'Cerimonia', 'description' => 'Cerimonia funebre', 'order' => 5, 'is_required' => true, 'estimated_duration_hours' => 2],
            ['name' => 'Sepoltura', 'description' => 'Sepoltura o cremazione', 'order' => 6, 'is_required' => true, 'estimated_duration_hours' => 3],
        ];

        foreach ($agency1Steps as $stepData) {
            TimelineStep::create(array_merge($stepData, ['agency_id' => 1]));
        }

        // Template steps per Agenzia 2 (Grande) - più dettagliati
        $agency2Steps = [
            ['name' => 'Primo contatto famiglia', 'description' => 'Colloquio iniziale con la famiglia', 'order' => 1, 'is_required' => true, 'estimated_duration_hours' => 1],
            ['name' => 'Ritiro salma', 'description' => 'Ritiro della salma con mezzo refrigerato', 'order' => 2, 'is_required' => true, 'estimated_duration_hours' => 2],
            ['name' => 'Tanatoprassi', 'description' => 'Trattamento conservativo della salma', 'order' => 3, 'is_required' => false, 'estimated_duration_hours' => 3],
            ['name' => 'Vestizione e trucco', 'description' => 'Vestizione e trucco del defunto', 'order' => 4, 'is_required' => true, 'estimated_duration_hours' => 2],
            ['name' => 'Allestimento camera ardente', 'description' => 'Preparazione camera ardente', 'order' => 5, 'is_required' => false, 'estimated_duration_hours' => 1],
            ['name' => 'Documenti amministrativi', 'description' => 'Pratiche Comune, ASL, cimitero', 'order' => 6, 'is_required' => true, 'estimated_duration_hours' => 6],
            ['name' => 'Stampa materiali', 'description' => 'Manifesti, santini, libretti', 'order' => 7, 'is_required' => false, 'estimated_duration_hours' => 3],
            ['name' => 'Affissioni', 'description' => 'Affissione manifesti nel territorio', 'order' => 8, 'is_required' => false, 'estimated_duration_hours' => 2],
            ['name' => 'Coordinamento cerimonia', 'description' => 'Organizzazione con chiesa/celebrante', 'order' => 9, 'is_required' => true, 'estimated_duration_hours' => 2],
            ['name' => 'Cerimonia religiosa', 'description' => 'Cerimonia in chiesa o altro luogo di culto', 'order' => 10, 'is_required' => false, 'estimated_duration_hours' => 2],
            ['name' => 'Trasporto al cimitero', 'description' => 'Trasferimento con corteo funebre', 'order' => 11, 'is_required' => true, 'estimated_duration_hours' => 1],
            ['name' => 'Sepoltura/Cremazione', 'description' => 'Ultima deposizione', 'order' => 12, 'is_required' => true, 'estimated_duration_hours' => 2],
            ['name' => 'Follow-up famiglia', 'description' => 'Consegna documenti e supporto post-funerale', 'order' => 13, 'is_required' => true, 'estimated_duration_hours' => 1],
        ];

        foreach ($agency2Steps as $stepData) {
            TimelineStep::create(array_merge($stepData, ['agency_id' => 2]));
        }

        $this->command->info('✅ Seeded timeline step templates');
        $this->command->info('   - Agency 1 (Piccola): 6 steps');
        $this->command->info('   - Agency 2 (Grande): 13 steps');
    }
}
