<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GapoktanPoktanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Gapoktan Sangapati
        $gapoktanId = DB::table('gapoktan')->insertGetId([
            'name' => 'Gapoktan Sangapati',
            'code' => 'GSP-001',
            'address' => 'Jl. Raya Tanjung, Kecamatan Tanjung',
            'village' => 'Tanjung',
            'district' => 'Tanjung',
            'province' => 'Nusa Tenggara Barat',
            'phone' => '081234567890',
            'email' => 'sangapati@gmail.com',
            'chairman_id' => null, // Will be updated after users seeder
            'established_date' => '2015-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create 5 Poktans in different villages
        $poktans = [
            [
                'name' => 'Poktan Makmur Malaka',
                'code' => 'PKT-MLK-001',
                'village' => 'Malaka',
                'total_members' => 25,
            ],
            [
                'name' => 'Poktan Sejahtera Tanjung',
                'code' => 'PKT-TJG-001',
                'village' => 'Tanjung',
                'total_members' => 30,
            ],
            [
                'name' => 'Poktan Berkah Sokong',
                'code' => 'PKT-SKG-001',
                'village' => 'Sokong',
                'total_members' => 20,
            ],
            [
                'name' => 'Poktan Maju Bentek',
                'code' => 'PKT-BTK-001',
                'village' => 'Bentek',
                'total_members' => 22,
            ],
            [
                'name' => 'Poktan Subur Sesait',
                'code' => 'PKT-SST-001',
                'village' => 'Sesait',
                'total_members' => 28,
            ],
        ];

        foreach ($poktans as $poktan) {
            DB::table('poktans')->insert([
                'name' => $poktan['name'],
                'code' => $poktan['code'],
                'village' => $poktan['village'],
                'chairman_id' => null, // Will be updated after users seeder
                'total_members' => $poktan['total_members'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Created 1 Gapoktan and 5 Poktans');
    }
}
