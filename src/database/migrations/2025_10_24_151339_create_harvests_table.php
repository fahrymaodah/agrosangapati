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
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('poktan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_id')->constrained('commodity_grades')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 20);
            $table->date('harvest_date');
            $table->string('harvest_photo')->nullable();
            $table->enum('status', ['stored', 'sold', 'damaged'])->default('stored');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['poktan_id', 'harvest_date']);
            $table->index(['member_id', 'harvest_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
