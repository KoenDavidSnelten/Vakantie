<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vacation_ski_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price_ski_pass', 8, 2)->nullable();
            $table->decimal('distance_to_slopes_km', 5, 1)->nullable();
            $table->boolean('has_bus')->default(false);
            $table->string('ski_area_map_path')->nullable();
            $table->string('ski_area_map_url')->nullable();
            $table->timestamps();
        });

        Schema::create('vacation_ski_area_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_ski_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value')->default(1);
            $table->timestamps();

            $table->unique(['vacation_ski_area_id', 'user_id']);
        });

        Schema::create('vacation_ski_area_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_ski_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('vacation_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_ski_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price_accommodation_per_night', 8, 2)->nullable();
            $table->string('price_accommodation_unit')->nullable();
            $table->text('room_layout')->nullable();
            $table->timestamps();
        });

        Schema::create('vacation_hotel_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value')->default(1);
            $table->timestamps();

            $table->unique(['vacation_hotel_id', 'user_id']);
        });

        Schema::create('vacation_hotel_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        $this->migrateExistingLocationsIntoSkiAreasAndHotels();

        Schema::dropIfExists('vacation_location_comments');
        Schema::dropIfExists('vacation_location_votes');
        Schema::dropIfExists('vacation_locations');
    }

    /**
     * The old flat "location" model conflated a ski area (ski pass price, map,
     * distance, bus) with a specific hotel. Existing rows get grouped into one
     * ski area per vacation, named after the first location, since in practice
     * they referred to the same resort — hotel ids are kept identical to the
     * original location ids so votes/comments carry over untouched.
     */
    private function migrateExistingLocationsIntoSkiAreasAndHotels(): void
    {
        if (! Schema::hasTable('vacation_locations')) {
            return;
        }

        $locations = DB::table('vacation_locations')->orderBy('id')->get();

        foreach ($locations->groupBy('vacation_id') as $vacationId => $group) {
            $first = $group->first();

            $skiAreaId = DB::table('vacation_ski_areas')->insertGetId([
                'vacation_id' => $vacationId,
                'user_id' => $first->user_id,
                'name' => $first->name,
                'price_ski_pass' => $group->pluck('price_ski_pass')->filter()->first(),
                'distance_to_slopes_km' => $group->pluck('distance_to_slopes_km')->filter()->first(),
                'has_bus' => (bool) $group->max('has_bus'),
                'ski_area_map_path' => $group->pluck('ski_area_map_path')->filter()->first(),
                'ski_area_map_url' => $group->pluck('ski_area_map_url')->filter()->first(),
                'created_at' => $first->created_at,
                'updated_at' => now(),
            ]);

            foreach ($group as $location) {
                DB::table('vacation_hotels')->insert([
                    'id' => $location->id,
                    'vacation_ski_area_id' => $skiAreaId,
                    'user_id' => $location->user_id,
                    'name' => $location->name,
                    'url' => $location->url,
                    'image_url' => $location->image_url,
                    'price_accommodation_per_night' => $location->price_accommodation_per_night,
                    'price_accommodation_unit' => $location->price_accommodation_unit,
                    'room_layout' => $location->room_layout,
                    'created_at' => $location->created_at,
                    'updated_at' => $location->updated_at,
                ]);
            }
        }

        DB::table('vacation_location_votes')->get()->each(function ($vote) {
            DB::table('vacation_hotel_votes')->insert([
                'vacation_hotel_id' => $vote->vacation_location_id,
                'user_id' => $vote->user_id,
                'value' => $vote->value,
                'created_at' => $vote->created_at,
                'updated_at' => $vote->updated_at,
            ]);
        });

        DB::table('vacation_location_comments')->get()->each(function ($comment) {
            DB::table('vacation_hotel_comments')->insert([
                'vacation_hotel_id' => $comment->vacation_location_id,
                'user_id' => $comment->user_id,
                'body' => $comment->body,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_hotel_comments');
        Schema::dropIfExists('vacation_hotel_votes');
        Schema::dropIfExists('vacation_hotels');
        Schema::dropIfExists('vacation_ski_area_comments');
        Schema::dropIfExists('vacation_ski_area_votes');
        Schema::dropIfExists('vacation_ski_areas');
    }
};
