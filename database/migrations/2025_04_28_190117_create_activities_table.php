<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('description');
            $table->string('url')->nullable();
            $table->decimal('price_by_person', 10, 2)->nullable();
            $table->decimal('price_by_group', 10, 2)->nullable();
            $table->string('place_name')->nullable();
            $table->string('place_latitude')->nullable();
            $table->string('place_longitude')->nullable();
            $table->json('place_geojson')->nullable();
            $table->foreignUuid('travel_id')->constrained('travel')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
