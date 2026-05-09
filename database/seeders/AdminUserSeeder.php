<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', '');
        $password = (string) env('ADMIN_PASSWORD', '');
        $name = (string) env('ADMIN_NAME', 'Admin');

        if ($email === '' || $password === '') {
            $this->command->warn('AdminUserSeeder: ADMIN_EMAIL or ADMIN_PASSWORD is empty — skipping.');

            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        $this->command->info($user->wasRecentlyCreated
            ? "AdminUserSeeder: created admin {$email}."
            : "AdminUserSeeder: admin {$email} already exists, skipping.");
    }
}
