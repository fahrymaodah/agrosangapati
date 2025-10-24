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
        Schema::table('poktans', function (Blueprint $table) {
            $table->foreignId('gapoktan_id')->nullable()->after('id')->constrained('gapoktan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poktans', function (Blueprint $table) {
            $table->dropForeign(['gapoktan_id']);
            $table->dropColumn('gapoktan_id');
        });
    }
};
