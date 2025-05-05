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
        Schema::create('housings', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->decimal('price_by_person', 10, 2)->nullable();
            $table->decimal('price_by_group', 10, 2)->nullable();
            $table->string('place_name')->nullable();
            $table->string('place_latitude')->nullable();
            $table->string('place_longitude')->nullable();
            $table->date('start_date')->nullable()->after('place_geojson');
            $table->date('end_date')->nullable()->after('start_date');
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->foreignUuid('travel_id')->constrained('travel')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housings');
    }
};
