<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Тестовый пользователь',
                'username' => 'test',
                'password' => 'test',
            ],
        );

        $this->command->info($user->wasRecentlyCreated
            ? 'TestUserSeeder: created test user (login: test or test@example.com).'
            : 'TestUserSeeder: test user already exists, skipping.');
    }
}
