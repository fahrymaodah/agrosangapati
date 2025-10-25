<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommoditySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Common commodities in Lombok
        $commodities = [
            [
                'name' => 'Padi',
                'unit' => 'kg',
                'current_market_price' => 6000,
                'description' => 'Padi GKP (Gabah Kering Panen) kualitas premium',
            ],
            [
                'name' => 'Jagung',
                'unit' => 'kg',
                'current_market_price' => 4500,
                'description' => 'Jagung pipilan kering kualitas ekspor',
            ],
            [
                'name' => 'Cabai Merah',
                'unit' => 'kg',
                'current_market_price' => 35000,
                'description' => 'Cabai merah besar keriting segar',
            ],
            [
                'name' => 'Tomat',
                'unit' => 'kg',
                'current_market_price' => 8000,
                'description' => 'Tomat merah segar kualitas super',
            ],
            [
                'name' => 'Bawang Merah',
                'unit' => 'kg',
                'current_market_price' => 45000,
                'description' => 'Bawang merah varietas lokal Lombok',
            ],
        ];

        foreach ($commodities as $commodity) {
            $commodityId = DB::table('commodities')->insertGetId([
                'name' => $commodity['name'],
                'unit' => $commodity['unit'],
                'current_market_price' => $commodity['current_market_price'],
                'description' => $commodity['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create grades for each commodity (A, B, C)
            $grades = [
                [
                    'grade_name' => 'Grade A (Super)',
                    'price_modifier' => 15, // +15% from base price
                    'description' => 'Kualitas terbaik, ukuran besar, tidak ada cacat',
                ],
                [
                    'grade_name' => 'Grade B (Sedang)',
                    'price_modifier' => 0, // 0% = base price
                    'description' => 'Kualitas sedang, ukuran sedang, cacat minimal',
                ],
                [
                    'grade_name' => 'Grade C (Ekonomis)',
                    'price_modifier' => -15, // -15% from base price
                    'description' => 'Kualitas standar, ukuran kecil atau cacat ringan',
                ],
            ];

            foreach ($grades as $grade) {
                DB::table('commodity_grades')->insert([
                    'commodity_id' => $commodityId,
                    'grade_name' => $grade['grade_name'],
                    'price_modifier' => $grade['price_modifier'],
                    'description' => $grade['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✓ Created 5 commodities with 3 grades each (total 15 grades)');
    }
}
