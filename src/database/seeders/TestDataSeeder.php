<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Poktan;
use App\Models\Gapoktan;
use App\Models\CashBalance;
use App\Models\Transaction;
use App\Models\TransactionCategory;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Gapoktan if not exists
        $gapoktan = Gapoktan::firstOrCreate([
            'code' => 'GAP001',
        ], [
            'name' => 'Gapoktan Test',
            'address' => 'Test Address',
            'village' => 'Test Village',
            'district' => 'Test District',
            'province' => 'Test Province',
            'phone' => '081234567890',
            'email' => 'test@gapoktan.com',
        ]);

        // Create or update Poktan with CashBalance
        $poktans = [
            ['name' => 'Poktan Makmur', 'code' => 'PKT001'],
            ['name' => 'Poktan Sejahtera', 'code' => 'PKT002'],
            ['name' => 'Poktan Maju', 'code' => 'PKT003'],
        ];

        foreach ($poktans as $poktanData) {
            $poktan = Poktan::firstOrCreate(
                ['code' => $poktanData['code']],
                [
                    'name' => $poktanData['name'],
                    'village' => 'Test Village',
                    'gapoktan_id' => $gapoktan->id,
                    'total_members' => 10,
                    'status' => 'active',
                ]
            );

            // Create or update CashBalance for each poktan
            CashBalance::updateOrCreate(
                ['poktan_id' => $poktan->id],
                [
                    'balance' => rand(1000000, 5000000),
                    'last_updated' => now(),
                ]
            );
        }

        // Create sample transaction categories
        $incomeCategory = TransactionCategory::firstOrCreate([
            'name' => 'Penjualan Hasil Panen',
            'type' => 'income'
        ]);
        
        $expenseCategory = TransactionCategory::firstOrCreate([
            'name' => 'Biaya Operasional',
            'type' => 'expense'
        ]);

        // Create sample transactions for testing
        $poktanIds = Poktan::pluck('id');
        foreach ($poktanIds as $poktanId) {
            // Add some income transactions
            Transaction::firstOrCreate([
                'poktan_id' => $poktanId,
                'transaction_type' => 'income',
                'transaction_date' => now()->startOfMonth(),
            ], [
                'amount' => rand(500000, 2000000),
                'description' => 'Penjualan Hasil Panen',
                'category_id' => $incomeCategory->id,
                'status' => 'approved',
                'created_by' => 1, // Assuming admin user with ID 1
            ]);
            
            // Add some expense transactions
            Transaction::firstOrCreate([
                'poktan_id' => $poktanId,
                'transaction_type' => 'expense', 
                'transaction_date' => now()->startOfMonth()->addDays(5),
            ], [
                'amount' => rand(200000, 800000),
                'description' => 'Biaya Operasional',
                'category_id' => $expenseCategory->id,
                'status' => 'approved',
                'created_by' => 1, // Assuming admin user with ID 1
            ]);
        }

        echo "Test data created successfully!\n";
    }
}
