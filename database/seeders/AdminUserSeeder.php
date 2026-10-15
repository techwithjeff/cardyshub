<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@cardyshub.uk');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command?->warn('ADMIN_PASSWORD is not set in .env — skipping admin user creation.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Cardyshub Admin',
                'password' => $password,
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
