<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@jar.local'],
            [
                'name' => 'Admin User',
                'phone' => '+359878369024',
                'preferred_contact_channel' => 'email',
                'password' => 'Admin12345',
                'role' => 'admin',
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'email_verification_expires_at' => null,
            ],
        );
    }
}
