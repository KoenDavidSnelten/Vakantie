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
        Schema::create('vacation_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('car_model');
            $table->unsignedTinyInteger('seats')->nullable();
            $table->boolean('has_winter_tires')->default(false);
            $table->boolean('has_large_trunk')->default(false);
            $table->boolean('has_roof_box')->default(false);
            $table->decimal('price_per_day', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_vehicles');
    }
};
