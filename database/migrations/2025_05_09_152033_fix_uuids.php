<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->uuid('id')->primary()->change();
        });

        Schema::table('housings', function (Blueprint $table) {
            $table->uuid('id')->primary()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        Schema::table('housings', function (Blueprint $table) {
            $table->uuid('id')->change();
        });
    }
};
