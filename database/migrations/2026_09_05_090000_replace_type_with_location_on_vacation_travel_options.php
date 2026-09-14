<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Een reisoptie had een vast type (OV/bus/trein). Dat zei weinig, want het
     * staat toch al in de naam. In plaats daarvan wijs je de optie aan een
     * skigebied toe dat al in de locatieplanner staat, zodat duidelijk is waar
     * de reis heen gaat.
     */
    public function up(): void
    {
        Schema::table('vacation_travel_options', function (Blueprint $table) {
            $table->foreignId('vacation_ski_area_id')
                ->nullable()
                ->after('user_id')
                ->constrained('vacation_ski_areas')
                ->nullOnDelete();
        });

        Schema::table('vacation_travel_options', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_travel_options', function (Blueprint $table) {
            $table->string('type')->default('trein');
        });

        Schema::table('vacation_travel_options', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vacation_ski_area_id');
        });
    }
};
