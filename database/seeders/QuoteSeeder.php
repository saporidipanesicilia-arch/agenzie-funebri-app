<?php

namespace Database\Seeders;

use App\Models\Funeral;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Quote 1: Agenzia 1, Funeral 1 (Giuseppe Verdi) - Margine buono
        $funeral1 = Funeral::where('agency_id', 1)->first();

        if ($funeral1) {
            $quote1 = Quote::create([
                'funeral_id' => $funeral1->id,
                'agency_id' => $funeral1->agency_id,
                'branch_id' => $funeral1->branch_id,
                'quote_number' => '2024/0001',
                'status' => 'sent',
                'valid_until' => now()->addDays(15),
                'created_by_user_id' => 1,
                'sent_at' => now()->subDays(1),
            ]);

            // Items per quote 1
            QuoteItem::create([
                'quote_id' => $quote1->id,
                'item_type' => 'coffin',
                'description' => 'Cofano impiallacciato noce con maniglie dorate',
                'cost_price' => 800.00,
                'selling_price' => 1200.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote1->id,
                'item_type' => 'flowers',
                'description' => 'Corona di fiori bianchi',
                'cost_price' => 100.00,
                'selling_price' => 180.00,
                'quantity' => 2,
            ]);

            QuoteItem::create([
                'quote_id' => $quote1->id,
                'item_type' => 'transport',
                'description' => 'Trasporto con carro funebre',
                'cost_price' => 150.00,
                'selling_price' => 250.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote1->id,
                'item_type' => 'service',
                'description' => 'Vestizione e preparazione salma',
                'cost_price' => 80.00,
                'selling_price' => 150.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote1->id,
                'item_type' => 'documents',
                'description' => 'Pratiche amministrative complete',
                'cost_price' => 100.00,
                'selling_price' => 200.00,
                'quantity' => 1,
            ]);

            $this->command->info('   Quote 1: Total cost €' . number_format($quote1->total_cost, 2));
            $this->command->info('   Quote 1: Total selling €' . number_format($quote1->total_selling, 2));
            $this->command->info('   Quote 1: Margin ' . number_format($quote1->margin_percentage, 2) . '% (' . $quote1->margin_color . ')');
        }

        // Quote 2: Agenzia 2, Funeral 2 (Anna Colombo) - Margine basso
        $funeral2 = Funeral::where('agency_id', 2)->skip(0)->first();

        if ($funeral2) {
            $quote2 = Quote::create([
                'funeral_id' => $funeral2->id,
                'agency_id' => $funeral2->agency_id,
                'branch_id' => $funeral2->branch_id,
                'quote_number' => '2024/0001',
                'status' => 'draft',
                'valid_until' => now()->addDays(30),
                'discount_percentage' => 10.00, // 10% discount → margine si riduce
                'created_by_user_id' => 4,
            ]);

            // Items per quote 2 (cremation)
            QuoteItem::create([
                'quote_id' => $quote2->id,
                'item_type' => 'coffin',
                'description' => 'Cofano per cremazione in legno massello',
                'cost_price' => 1200.00,
                'selling_price' => 1600.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote2->id,
                'item_type' => 'service',
                'description' => 'Tanatoprassi conservativa',
                'cost_price' => 300.00,
                'selling_price' => 450.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote2->id,
                'item_type' => 'service',
                'description' => 'Vestizione e trucco professionale',
                'cost_price' => 120.00,
                'selling_price' => 200.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote2->id,
                'item_type' => 'transport',
                'description' => 'Trasporto refrigerato al crematorio',
                'cost_price' => 200.00,
                'selling_price' => 320.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote2->id,
                'item_type' => 'ceremony',
                'description' => 'Cremazione presso tempio crematorio',
                'cost_price' => 600.00,
                'selling_price' => 800.00,
                'quantity' => 1,
            ]);

            QuoteItem::create([
                'quote_id' => $quote2->id,
                'item_type' => 'flowers',
                'description' => 'Composizione floreale per camera ardente',
                'cost_price' => 150.00,
                'selling_price' => 250.00,
                'quantity' => 1,
            ]);

            $this->command->info('   Quote 2: Total cost €' . number_format($quote2->total_cost, 2));
            $this->command->info('   Quote 2: Total selling €' . number_format($quote2->total_selling, 2));
            $this->command->info('   Quote 2: Discount -€' . number_format($quote2->discount_applied, 2));
            $this->command->info('   Quote 2: Final total €' . number_format($quote2->final_total, 2));
            $this->command->info('   Quote 2: Margin ' . number_format($quote2->margin_percentage, 2) . '% (' . $quote2->margin_color . ')');
        }

        $this->command->info('✅ Seeded 2 sample quotes');
    }
}
