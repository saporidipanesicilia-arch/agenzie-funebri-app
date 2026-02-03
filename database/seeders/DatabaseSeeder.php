<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AgencySeeder::class,
            TimelineStepSeeder::class,
            DocumentTypeSeeder::class,
            FuneralSeeder::class,
            MarginSettingsSeeder::class,
            QuoteSeeder::class,
            CemeterySeeder::class,
            FamilyAccessSeeder::class,
        ]);
    }
}
