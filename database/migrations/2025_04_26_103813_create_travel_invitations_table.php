<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('travel_id')->constrained('travel')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('token');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_invitations');
    }
};
