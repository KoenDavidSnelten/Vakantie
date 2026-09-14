<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wie rijdt met wie mee. Eén rij per persoon per auto; de controller bewaakt
     * dat iemand binnen dezelfde vakantie maar in één auto tegelijk zit.
     */
    public function up(): void
    {
        Schema::create('vacation_vehicle_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['vacation_vehicle_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_vehicle_passengers');
    }
};
