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
        Schema::create('commodity_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commodity_id')->constrained()->cascadeOnDelete();
            $table->string('grade_name'); // A, B, C atau Premium, Standard, dll
            $table->decimal('price_modifier', 5, 2)->default(0); // persentase dari harga pasar
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('commodity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commodity_grades');
    }
};
