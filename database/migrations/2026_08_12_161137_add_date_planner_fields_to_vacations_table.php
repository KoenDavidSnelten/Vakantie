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
        Schema::table('vacations', function (Blueprint $table) {
            $table->date('planning_start_date')->nullable()->after('phase');
            $table->date('planning_end_date')->nullable()->after('planning_start_date');
            $table->date('final_start_date')->nullable()->after('planning_end_date');
            $table->date('final_end_date')->nullable()->after('final_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropColumn([
                'planning_start_date',
                'planning_end_date',
                'final_start_date',
                'final_end_date',
            ]);
        });
    }
};
