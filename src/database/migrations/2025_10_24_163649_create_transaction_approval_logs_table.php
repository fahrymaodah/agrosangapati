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
        Schema::create('transaction_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->enum('action', ['requested', 'approved', 'rejected', 'updated']);
            $table->enum('previous_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->enum('new_status', ['pending', 'approved', 'rejected']);
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For additional data like IP address, device, etc.
            $table->timestamps();
            $table->softDeletes();

            // Indexes for faster queries
            $table->index(['transaction_id', 'action']);
            $table->index('performed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_approval_logs');
    }
};
