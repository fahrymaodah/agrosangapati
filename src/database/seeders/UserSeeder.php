<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $poktans = DB::table('poktans')->get();

        // 1. Superadmin (not tied to any poktan)
        DB::table('users')->insert([
            'name' => 'Super Administrator',
            'email' => 'admin@agrosangapati.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'poktan_id' => null,
            'phone' => '081234567001',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Ketua Gapoktan
        $ketuaGapoktanId = DB::table('users')->insertGetId([
            'name' => 'H. Ahmad Fauzi',
            'email' => 'ketua@sangapati.com',
            'password' => Hash::make('password'),
            'role' => 'ketua_gapoktan',
            'poktan_id' => null,
            'phone' => '081234567002',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update gapoktan chairman
        DB::table('gapoktan')->where('code', 'GSP-001')->update([
            'chairman_id' => $ketuaGapoktanId,
        ]);

        // 3. Pengurus Gapoktan (2 people)
        $pengurusGapoktan = [
            ['name' => 'Hj. Siti Aminah', 'email' => 'bendahara@sangapati.com', 'phone' => '081234567003'],
            ['name' => 'Muhammad Yusuf', 'email' => 'sekretaris@sangapati.com', 'phone' => '081234567004'],
        ];

        foreach ($pengurusGapoktan as $pengurus) {
            DB::table('users')->insert([
                'name' => $pengurus['name'],
                'email' => $pengurus['email'],
                'password' => Hash::make('password'),
                'role' => 'pengurus_gapoktan',
                'poktan_id' => null,
                'phone' => $pengurus['phone'],
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Ketua Poktan for each poktan
        $ketuaPoktanData = [
            ['name' => 'Amaq Ridwan', 'email' => 'ketua.malaka@sangapati.com'],
            ['name' => 'Amaq Jalaludin', 'email' => 'ketua.tanjung@sangapati.com'],
            ['name' => 'Amaq Suryadi', 'email' => 'ketua.sokong@sangapati.com'],
            ['name' => 'Amaq Nasrudin', 'email' => 'ketua.bentek@sangapati.com'],
            ['name' => 'Amaq Fikri', 'email' => 'ketua.sesait@sangapati.com'],
        ];

        $phoneNumber = 5;
        foreach ($poktans as $index => $poktan) {
            $ketuaId = DB::table('users')->insertGetId([
                'name' => $ketuaPoktanData[$index]['name'],
                'email' => $ketuaPoktanData[$index]['email'],
                'password' => Hash::make('password'),
                'role' => 'ketua_poktan',
                'poktan_id' => $poktan->id,
                'phone' => '08123456700' . $phoneNumber,
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update poktan chairman
            DB::table('poktans')->where('id', $poktan->id)->update([
                'chairman_id' => $ketuaId,
            ]);

            $phoneNumber++;
        }

        // 5. Pengurus Poktan (2 per poktan = 10 people)
        $phoneNumber = 10;
        foreach ($poktans as $poktan) {
            for ($i = 1; $i <= 2; $i++) {
                DB::table('users')->insert([
                    'name' => "Pengurus {$poktan->village} {$i}",
                    'email' => "pengurus{$i}.{$poktan->code}@sangapati.com",
                    'password' => Hash::make('password'),
                    'role' => 'pengurus_poktan',
                    'poktan_id' => $poktan->id,
                    'phone' => '08123456700' . $phoneNumber,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $phoneNumber++;
            }
        }

        // 6. Anggota Poktan (4 per poktan = 20 people)
        foreach ($poktans as $poktan) {
            for ($i = 1; $i <= 4; $i++) {
                DB::table('users')->insert([
                    'name' => "Anggota {$poktan->village} {$i}",
                    'email' => "anggota{$i}.{$poktan->code}@sangapati.com",
                    'password' => Hash::make('password'),
                    'role' => 'anggota_poktan',
                    'poktan_id' => $poktan->id,
                    'phone' => '08123456700' . $phoneNumber,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $phoneNumber++;
            }
        }

        $this->command->info('✓ Created 39 users (1 superadmin, 1 ketua gapoktan, 2 pengurus gapoktan, 5 ketua poktan, 10 pengurus poktan, 20 anggota poktan)');
    }
}
