<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Poktan;

class AuthTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing poktans
        $poktanTaniMakmur = Poktan::where('code', 'PTM-001')->first();
        $poktanHarapanBaru = Poktan::where('code', 'PHB-002')->first();
        $poktanSejahteraBersama = Poktan::where('code', 'PSB-003')->first();

        // Create test users for authentication
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@agrosangapati.com',
                'phone' => '081234567890',
                'password' => Hash::make('Password123!'),
                'role' => 'superadmin',
                'poktan_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ketua Gapoktan Sangapati',
                'email' => 'ketua.gapoktan@agrosangapati.com',
                'phone' => '081234567891',
                'password' => Hash::make('Password123!'),
                'role' => 'ketua_gapoktan',
                'poktan_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pengurus Gapoktan Sangapati',
                'email' => 'pengurus.gapoktan@agrosangapati.com',
                'phone' => '081234567892',
                'password' => Hash::make('Password123!'),
                'role' => 'pengurus_gapoktan',
                'poktan_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        ];

        // Add Poktan-level users if poktans exist
        if ($poktanTaniMakmur) {
            $users[] = [
                'name' => 'Ketua Poktan Tani Makmur',
                'email' => 'ketua.tanimakmur@agrosangapati.com',
                'phone' => '081234567893',
                'password' => Hash::make('Password123!'),
                'role' => 'ketua_poktan',
                'poktan_id' => $poktanTaniMakmur->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];

            $users[] = [
                'name' => 'Pengurus Poktan Tani Makmur',
                'email' => 'pengurus.tanimakmur@agrosangapati.com',
                'phone' => '081234567894',
                'password' => Hash::make('Password123!'),
                'role' => 'pengurus_poktan',
                'poktan_id' => $poktanTaniMakmur->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];

            $users[] = [
                'name' => 'Anggota Poktan Tani Makmur 1',
                'email' => 'anggota1.tanimakmur@agrosangapati.com',
                'phone' => '081234567895',
                'password' => Hash::make('Password123!'),
                'role' => 'anggota_poktan',
                'poktan_id' => $poktanTaniMakmur->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];
        }

        if ($poktanHarapanBaru) {
            $users[] = [
                'name' => 'Ketua Poktan Harapan Baru',
                'email' => 'ketua.harapanbaru@agrosangapati.com',
                'phone' => '081234567896',
                'password' => Hash::make('Password123!'),
                'role' => 'ketua_poktan',
                'poktan_id' => $poktanHarapanBaru->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];

            $users[] = [
                'name' => 'Anggota Poktan Harapan Baru 1',
                'email' => 'anggota1.harapanbaru@agrosangapati.com',
                'phone' => '081234567897',
                'password' => Hash::make('Password123!'),
                'role' => 'anggota_poktan',
                'poktan_id' => $poktanHarapanBaru->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];
        }

        if ($poktanSejahteraBersama) {
            $users[] = [
                'name' => 'Ketua Poktan Sejahtera Bersama',
                'email' => 'ketua.sejahterabersama@agrosangapati.com',
                'phone' => '081234567898',
                'password' => Hash::make('Password123!'),
                'role' => 'ketua_poktan',
                'poktan_id' => $poktanSejahteraBersama->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];

            $users[] = [
                'name' => 'Anggota Poktan Sejahtera Bersama 1',
                'email' => 'anggota1.sejahterabersama@agrosangapati.com',
                'phone' => '081234567899',
                'password' => Hash::make('Password123!'),
                'role' => 'anggota_poktan',
                'poktan_id' => $poktanSejahteraBersama->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];
        }

        // Create users
        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ Auth test users created successfully!');
        $this->command->info('📧 All passwords: Password123!');
        $this->command->info('');
        $this->command->info('Test Credentials:');
        $this->command->info('------------------');
        foreach ($users as $user) {
            $this->command->info("Email: {$user['email']} | Role: {$user['role']}");
        }
    }
}
