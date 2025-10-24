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
        Schema::create('cash_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poktan_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamp('last_updated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_balances');
    }
};
