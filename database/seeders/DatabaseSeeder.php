<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Local demo state (guides/testing.md: seeding must always produce a
     * demo-ready site — never run in production). Password for all: "password".
     */
    public function run(): void
    {
        User::factory()->developer()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->count(5)->create();
        User::factory()->unapproved()->count(2)->create();
    }
}
