<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laat iedereen zijn eigen avatar samenstellen: een symbool (of de initialen),
 * een achtergrondkleur en een randje. Alles nullable, zodat bestaande accounts
 * op de afgeleide standaardkleur met initialen blijven staan tot ze zelf iets
 * kiezen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_symbol')->nullable()->after('email');
            $table->string('avatar_color')->nullable()->after('avatar_symbol');
            $table->string('avatar_frame')->nullable()->after('avatar_color');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_symbol', 'avatar_color', 'avatar_frame']);
        });
    }
};
