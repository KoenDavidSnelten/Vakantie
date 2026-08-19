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
        Schema::create('vacation_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('price_type')->default('separate');
            $table->decimal('price_accommodation_per_night', 8, 2)->nullable();
            $table->decimal('price_ski_pass', 8, 2)->nullable();
            $table->decimal('price_combined', 8, 2)->nullable();
            $table->text('room_layout')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_locations');
    }
};
