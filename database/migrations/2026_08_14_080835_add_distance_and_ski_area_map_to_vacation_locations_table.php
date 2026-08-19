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
            $table->string('distance_to_slopes')->nullable()->after('room_layout');
            $table->string('ski_area_map_path')->nullable()->after('distance_to_slopes');
            $table->string('ski_area_map_url')->nullable()->after('ski_area_map_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_locations', function (Blueprint $table) {
            $table->dropColumn(['distance_to_slopes', 'ski_area_map_path', 'ski_area_map_url']);
        });
    }
};
