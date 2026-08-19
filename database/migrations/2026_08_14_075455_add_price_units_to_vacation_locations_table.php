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
        Schema::table('vacation_locations', function (Blueprint $table) {
            $table->string('price_accommodation_unit')->nullable()->after('price_accommodation_per_night');
            $table->string('price_combined_unit')->nullable()->after('price_combined');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_locations', function (Blueprint $table) {
            $table->dropColumn(['price_accommodation_unit', 'price_combined_unit']);
        });
    }
};
