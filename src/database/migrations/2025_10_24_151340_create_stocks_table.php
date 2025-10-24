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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poktan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_id')->constrained('commodity_grades')->restrictOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit', 20);
            $table->string('location')->nullable(); // gudang A, gudang B, atau null = gapoktan
            $table->timestamp('last_updated');
            $table->timestamps();
            
            $table->unique(['poktan_id', 'commodity_id', 'grade_id', 'location']);
            $table->index('poktan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
