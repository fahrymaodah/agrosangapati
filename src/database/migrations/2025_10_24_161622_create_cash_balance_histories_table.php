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
        Schema::create('cash_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poktan_id')->constrained('poktans')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->decimal('previous_balance', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->decimal('new_balance', 15, 2);
            $table->enum('type', ['income', 'expense']); // transaction type that caused the change
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('poktan_id');
            $table->index('transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_balance_histories');
    }
};
