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
        Schema::table('gapoktan', function (Blueprint $table) {
            $table->string('regency', 100)->after('district');
            $table->string('chairman', 255)->after('established_date');
            $table->string('chairman_phone', 20)->nullable()->after('chairman');
            $table->integer('total_poktans')->default(0)->after('established_date');
            $table->integer('total_members')->default(0)->after('total_poktans');
            $table->decimal('total_land_area', 10, 2)->default(0)->after('total_members');
            $table->string('registration_number', 100)->nullable()->after('total_land_area');
            $table->text('description')->nullable()->after('registration_number');
            $table->text('notes')->nullable()->after('description');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gapoktan', function (Blueprint $table) {
            $table->dropColumn([
                'regency',
                'chairman',
                'chairman_phone',
                'total_poktans',
                'total_members',
                'total_land_area',
                'registration_number',
                'description',
                'notes',
                'status'
            ]);
        });
    }
};
