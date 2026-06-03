<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $users = [
            ['name' => 'Alice',   'email' => 'alice@example.com'],
            ['name' => 'Bob',     'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
            ['name' => 'Diana',   'email' => 'diana@example.com'],
            ['name' => 'Ethan',   'email' => 'ethan@example.com'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => bcrypt('password')]
            );
        }
    }
}
