<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan',
                'anggota_poktan'
            ])->default('anggota_poktan')->after('email');
            
            $table->foreignId('poktan_id')
                ->nullable()
                ->after('role')
                ->constrained('poktans')
                ->nullOnDelete();
            
            $table->string('phone', 20)->nullable()->after('poktan_id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['poktan_id']);
            $table->dropColumn(['role', 'poktan_id', 'phone', 'status']);
        });
    }
};
