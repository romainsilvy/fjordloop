<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel', function (Blueprint $table) {
            $table->dropColumn('place_geojson');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('place_geojson');
        });
    }

    public function down(): void
    {
        Schema::table('travel', function (Blueprint $table) {
            $table->json('place_geojson')->nullable()->after('place_longitude');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->json('place_geojson')->nullable()->after('place_longitude');
        });
    }
};
