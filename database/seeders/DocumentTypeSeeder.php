<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Document types per Agenzia 1 (Piccola) - Base essenziali
        $agency1Types = [
            [
                'name' => 'Certificato di morte',
                'description' => 'Certificato medico attestante il decesso',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null, // Non scade
            ],
            [
                'name' => 'Permesso di sepoltura',
                'description' => 'Autorizzazione rilasciata dal Comune',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'entombment'],
                'expiry_days' => 7,
            ],
            [
                'name' => 'Permesso di cremazione',
                'description' => 'Autorizzazione alla cremazione',
                'is_required' => true,
                'required_for_service_types' => ['cremation'],
                'expiry_days' => 7,
            ],
            [
                'name' => 'Carta identità defunto',
                'description' => 'Documento di identità del defunto',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null,
            ],
            [
                'name' => 'Consenso familiari',
                'description' => 'Modulo firmato dai familiari',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null,
            ],
            [
                'name' => 'Foto defunto',
                'description' => 'Fotografia per manifesti e ricordo',
                'is_required' => false,
                'required_for_service_types' => null,
                'max_file_size_mb' => 5,
                'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            ],
        ];

        foreach ($agency1Types as $typeData) {
            DocumentType::create(array_merge($typeData, ['agency_id' => 1]));
        }

        // Document types per Agenzia 2 (Grande) - Più dettagliati
        $agency2Types = [
            [
                'name' => 'Certificato di morte',
                'description' => 'Certificato medico ISTAT completo',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null,
            ],
            [
                'name' => 'Scheda ISTAT',
                'description' => 'Scheda statistica ISTAT compilata',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => 7,
            ],
            [
                'name' => 'Permesso di sepoltura',
                'description' => 'Autorizzazione comunale alla sepoltura',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'entombment'],
                'expiry_days' => 10,
            ],
            [
                'name' => 'Permesso di cremazione',
                'description' => 'Autorizzazione del PM alla cremazione',
                'is_required' => true,
                'required_for_service_types' => ['cremation'],
                'expiry_days' => 10,
            ],
            [
                'name' => 'Certificato ASL',
                'description' => 'Certificato igienico-sanitario ASL',
                'is_required' => true,
                'required_for_service_types' => ['cremation'],
                'expiry_days' => 5,
            ],
            [
                'name' => 'Carta identità defunto',
                'description' => 'Documento di identità valido del defunto',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null,
            ],
            [
                'name' => 'Consenso familiari',
                'description' => 'Modulo consenso informato firmato',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null,
            ],
            [
                'name' => 'Dichiarazione aventi diritto',
                'description' => 'Dichiarazione sostitutiva atto notorietà',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'cremation', 'entombment'],
                'expiry_days' => null,
            ],
            [
                'name' => 'Concessione cimiteriale',
                'description' => 'Contratto di concessione tomba/loculo',
                'is_required' => true,
                'required_for_service_types' => ['burial', 'entombment'],
                'expiry_days' => 30,
            ],
            [
                'name' => 'Polizza assicurativa',
                'description' => 'Polizza infortuni attiva (se applicabile)',
                'is_required' => false,
                'required_for_service_types' => null,
                'expiry_days' => null,
            ],
            [
                'name' => 'Foto defunto',
                'description' => 'Fotografia alta risoluzione per manifesti',
                'is_required' => false,
                'required_for_service_types' => null,
                'max_file_size_mb' => 10,
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'tiff'],
            ],
            [
                'name' => 'Testamento biologico',
                'description' => 'Disposizioni anticipate di trattamento (se presenti)',
                'is_required' => false,
                'required_for_service_types' => null,
                'expiry_days' => null,
            ],
        ];

        foreach ($agency2Types as $typeData) {
            DocumentType::create(array_merge($typeData, ['agency_id' => 2]));
        }

        $this->command->info('✅ Seeded document types');
        $this->command->info('   - Agency 1 (Piccola): 6 document types');
        $this->command->info('   - Agency 2 (Grande): 12 document types');
    }
}
