<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Geüploade bestanden zijn vervangen: avatars worden uit de initialen
 * gegenereerd en een pistekaart is voortaan alleen nog een link. Daarmee
 * hoeft de app niets meer naar schijf te schrijven.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });

        Schema::table('vacation_ski_areas', function (Blueprint $table) {
            $table->dropColumn('ski_area_map_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('email');
        });

        Schema::table('vacation_ski_areas', function (Blueprint $table) {
            $table->string('ski_area_map_path')->nullable()->after('has_bus');
        });
    }
};
