<?php

namespace Database\Seeders;

use App\Models\Travel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Travel::factory()->count(10)->withOwner(User::first())->create();
    }
}
