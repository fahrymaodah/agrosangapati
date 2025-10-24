<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding AgroSangapati Database...');
        $this->command->newLine();

        // Order matters: Gapoktan & Poktans must be created before Users
        $this->call([
            GapoktanPoktanSeeder::class,
            UserSeeder::class,
            CommoditySeeder::class,
            TransactionCategorySeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('📧 Login credentials:');
        $this->command->info('   Superadmin: admin@agrosangapati.com / password');
        $this->command->info('   Ketua Gapoktan: ketua@sangapati.com / password');
    }
}
