<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Poktan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required IDs
        $poktan = Poktan::first();
        $incomeCategory = TransactionCategory::where('type', 'income')->first();
        $expenseCategory = TransactionCategory::where('type', 'expense')->first();
        $user = User::first();

        if (!$poktan || !$incomeCategory || !$expenseCategory || !$user) {
            $this->command->error('Required data not found. Please seed Poktan, TransactionCategory, and User first.');
            return;
        }

        // ========== OKTOBER 2025 (Bulan Lalu) ==========
        
        // Pemasukan Oktober (15 transaksi)
        $octoberIncomes = [
            ['date' => '2025-10-02', 'amount' => 2500000, 'desc' => 'Penjualan Padi'],
            ['date' => '2025-10-05', 'amount' => 1800000, 'desc' => 'Penjualan Jagung'],
            ['date' => '2025-10-08', 'amount' => 3200000, 'desc' => 'Penjualan Hasil Panen'],
            ['date' => '2025-10-12', 'amount' => 1500000, 'desc' => 'Penjualan Sayuran'],
            ['date' => '2025-10-15', 'amount' => 2800000, 'desc' => 'Penjualan Buah'],
            ['date' => '2025-10-18', 'amount' => 2200000, 'desc' => 'Penjualan Gabah'],
            ['date' => '2025-10-21', 'amount' => 1900000, 'desc' => 'Penjualan Beras'],
            ['date' => '2025-10-23', 'amount' => 3500000, 'desc' => 'Penjualan Hasil Kebun'],
            ['date' => '2025-10-25', 'amount' => 1600000, 'desc' => 'Penjualan Ternak'],
            ['date' => '2025-10-27', 'amount' => 2900000, 'desc' => 'Penjualan Produk Olahan'],
            ['date' => '2025-10-28', 'amount' => 1400000, 'desc' => 'Penjualan Pupuk Kompos'],
            ['date' => '2025-10-29', 'amount' => 2100000, 'desc' => 'Penjualan Bibit'],
            ['date' => '2025-10-30', 'amount' => 3300000, 'desc' => 'Penjualan Hasil Panen Raya'],
            ['date' => '2025-10-30', 'amount' => 1700000, 'desc' => 'Penjualan Sayur Organik'],
            ['date' => '2025-10-31', 'amount' => 2600000, 'desc' => 'Penjualan Akhir Bulan'],
        ];

        foreach ($octoberIncomes as $income) {
            Transaction::create([
                'poktan_id' => $poktan->id,
                'transaction_type' => 'income',
                'category_id' => $incomeCategory->id,
                'amount' => $income['amount'],
                'description' => $income['desc'],
                'transaction_date' => $income['date'],
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => Carbon::parse($income['date'])->addHours(2),
                'created_by' => $user->id,
            ]);
        }

        // Pengeluaran Oktober (12 transaksi)
        $octoberExpenses = [
            ['date' => '2025-10-03', 'amount' => 800000, 'desc' => 'Pembelian Pupuk'],
            ['date' => '2025-10-06', 'amount' => 650000, 'desc' => 'Pembelian Bibit'],
            ['date' => '2025-10-09', 'amount' => 1200000, 'desc' => 'Pembelian Pestisida'],
            ['date' => '2025-10-13', 'amount' => 500000, 'desc' => 'Bayar Upah Buruh'],
            ['date' => '2025-10-16', 'amount' => 900000, 'desc' => 'Perawatan Alat'],
            ['date' => '2025-10-19', 'amount' => 750000, 'desc' => 'Transportasi'],
            ['date' => '2025-10-22', 'amount' => 1100000, 'desc' => 'Pembelian Pakan Ternak'],
            ['date' => '2025-10-24', 'amount' => 600000, 'desc' => 'Listrik & Air'],
            ['date' => '2025-10-26', 'amount' => 850000, 'desc' => 'Pembelian Karung'],
            ['date' => '2025-10-28', 'amount' => 950000, 'desc' => 'Operasional Kantor'],
            ['date' => '2025-10-29', 'amount' => 700000, 'desc' => 'Bahan Bakar'],
            ['date' => '2025-10-31', 'amount' => 1300000, 'desc' => 'Pemeliharaan Lahan'],
        ];

        foreach ($octoberExpenses as $expense) {
            Transaction::create([
                'poktan_id' => $poktan->id,
                'transaction_type' => 'expense',
                'category_id' => $expenseCategory->id,
                'amount' => $expense['amount'],
                'description' => $expense['desc'],
                'transaction_date' => $expense['date'],
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => Carbon::parse($expense['date'])->addHours(2),
                'created_by' => $user->id,
            ]);
        }

        // ========== NOVEMBER 2025 (Bulan Ini) ==========
        
        // Pemasukan November (18 transaksi - lebih banyak)
        $novemberIncomes = [
            ['date' => '2025-11-01', 'amount' => 2700000, 'desc' => 'Penjualan Padi Premium'],
            ['date' => '2025-11-02', 'amount' => 1950000, 'desc' => 'Penjualan Jagung Manis'],
            ['date' => '2025-11-04', 'amount' => 3400000, 'desc' => 'Penjualan Hasil Panen Utama'],
            ['date' => '2025-11-05', 'amount' => 1650000, 'desc' => 'Penjualan Sayuran Organik'],
            ['date' => '2025-11-06', 'amount' => 2950000, 'desc' => 'Penjualan Buah Segar'],
            ['date' => '2025-11-07', 'amount' => 2350000, 'desc' => 'Penjualan Gabah Kering'],
            ['date' => '2025-11-08', 'amount' => 2050000, 'desc' => 'Penjualan Beras Premium'],
            ['date' => '2025-11-09', 'amount' => 3700000, 'desc' => 'Penjualan Hasil Kebun Raya'],
            ['date' => '2025-11-09', 'amount' => 1750000, 'desc' => 'Penjualan Ternak Unggas'],
            ['date' => '2025-11-09', 'amount' => 3100000, 'desc' => 'Penjualan Produk Olahan Premium'],
            ['date' => '2025-11-09', 'amount' => 1550000, 'desc' => 'Penjualan Pupuk Organik'],
            ['date' => '2025-11-09', 'amount' => 2250000, 'desc' => 'Penjualan Bibit Unggul'],
            ['date' => '2025-11-09', 'amount' => 3550000, 'desc' => 'Penjualan Hasil Panen Spesial'],
            ['date' => '2025-11-09', 'amount' => 1850000, 'desc' => 'Penjualan Sayur Hidroponik'],
            ['date' => '2025-11-09', 'amount' => 2800000, 'desc' => 'Penjualan Buah Import'],
            ['date' => '2025-11-09', 'amount' => 2450000, 'desc' => 'Penjualan Hasil Tambak'],
            ['date' => '2025-11-09', 'amount' => 3200000, 'desc' => 'Penjualan Produk Ekspor'],
            ['date' => '2025-11-09', 'amount' => 2900000, 'desc' => 'Penjualan Akhir Pekan'],
        ];

        foreach ($novemberIncomes as $income) {
            Transaction::create([
                'poktan_id' => $poktan->id,
                'transaction_type' => 'income',
                'category_id' => $incomeCategory->id,
                'amount' => $income['amount'],
                'description' => $income['desc'],
                'transaction_date' => $income['date'],
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => Carbon::parse($income['date'])->addHours(2),
                'created_by' => $user->id,
            ]);
        }

        // Pengeluaran November (10 transaksi - lebih sedikit)
        $novemberExpenses = [
            ['date' => '2025-11-02', 'amount' => 850000, 'desc' => 'Pembelian Pupuk Organik'],
            ['date' => '2025-11-03', 'amount' => 700000, 'desc' => 'Pembelian Bibit Unggul'],
            ['date' => '2025-11-05', 'amount' => 1250000, 'desc' => 'Pembelian Pestisida Modern'],
            ['date' => '2025-11-06', 'amount' => 550000, 'desc' => 'Bayar Upah Buruh Harian'],
            ['date' => '2025-11-07', 'amount' => 950000, 'desc' => 'Perawatan Mesin'],
            ['date' => '2025-11-08', 'amount' => 800000, 'desc' => 'Transportasi Distribusi'],
            ['date' => '2025-11-09', 'amount' => 1150000, 'desc' => 'Pembelian Pakan Premium'],
            ['date' => '2025-11-09', 'amount' => 650000, 'desc' => 'Listrik & Air November'],
            ['date' => '2025-11-09', 'amount' => 900000, 'desc' => 'Pembelian Kemasan'],
            ['date' => '2025-11-09', 'amount' => 1000000, 'desc' => 'Operasional & Admin'],
        ];

        foreach ($novemberExpenses as $expense) {
            Transaction::create([
                'poktan_id' => $poktan->id,
                'transaction_type' => 'expense',
                'category_id' => $expenseCategory->id,
                'amount' => $expense['amount'],
                'description' => $expense['desc'],
                'transaction_date' => $expense['date'],
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => Carbon::parse($expense['date'])->addHours(2),
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('✅ Transaction seeder completed!');
        $this->command->info('📊 Oktober: 15 pemasukan + 12 pengeluaran = 27 transaksi');
        $this->command->info('📊 November: 18 pemasukan + 10 pengeluaran = 28 transaksi');
        $this->command->info('💰 November lebih tinggi income & lebih rendah expense (trend positif!)');
    }
}
