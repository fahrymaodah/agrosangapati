<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default transaction categories (not tied to specific poktan)
        $categories = [
            // Income categories
            [
                'name' => 'Penjualan Hasil Bumi',
                'type' => 'income',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Iuran Anggota',
                'type' => 'income',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Bantuan Pemerintah',
                'type' => 'income',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Bantuan Swasta',
                'type' => 'income',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Pendapatan Lain-lain',
                'type' => 'income',
                'is_default' => true,
                'poktan_id' => null,
            ],
            // Expense categories
            [
                'name' => 'Pembelian Pupuk',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Pembelian Pestisida',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Pembelian Alat Pertanian',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Biaya Operasional',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Gaji dan Upah',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Biaya Transportasi',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
            [
                'name' => 'Pengeluaran Lain-lain',
                'type' => 'expense',
                'is_default' => true,
                'poktan_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('transaction_categories')->insert([
                'name' => $category['name'],
                'type' => $category['type'],
                'is_default' => $category['is_default'],
                'poktan_id' => $category['poktan_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Created 12 default transaction categories (5 income, 7 expense)');
    }
}
