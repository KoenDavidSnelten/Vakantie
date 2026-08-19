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
            $table->dropColumn('distance_to_slopes');
        });

        Schema::table('vacation_locations', function (Blueprint $table) {
            $table->decimal('distance_to_slopes_km', 5, 1)->nullable()->after('room_layout');
            $table->boolean('has_bus')->default(false)->after('distance_to_slopes_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_locations', function (Blueprint $table) {
            $table->dropColumn(['distance_to_slopes_km', 'has_bus']);
        });

        Schema::table('vacation_locations', function (Blueprint $table) {
            $table->string('distance_to_slopes')->nullable()->after('room_layout');
        });
    }
};
