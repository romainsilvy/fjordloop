<?php

namespace Database\Seeders;

use App\Models\Travel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            UserFactory::new([
                'email' => 'test1@email.com',
            ])->definition(),
        );

        Travel::factory()->count(10)->withOwner($user)->createActivities()->createHousings()->create();
    }
}
