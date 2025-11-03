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
        Schema::table('stocks', function (Blueprint $table) {
            $table->enum('quality_grade', ['A', 'B', 'C'])->default('B')->after('quantity');
            $table->enum('status', ['active', 'reserved', 'sold', 'damaged'])->default('active')->after('quality_grade');
            $table->date('harvest_date')->nullable()->after('status');
            $table->decimal('min_stock_alert', 10, 2)->default(10)->after('harvest_date');
            $table->text('notes')->nullable()->after('min_stock_alert');
            
            // Drop grade_id if exists since we're using quality_grade enum
            if (Schema::hasColumn('stocks', 'grade_id')) {
                $table->dropForeign(['grade_id']);
                $table->dropColumn('grade_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['quality_grade', 'status', 'harvest_date', 'min_stock_alert', 'notes']);
            $table->foreignId('grade_id')->nullable()->constrained('commodity_grades')->nullOnDelete();
        });
    }
};
