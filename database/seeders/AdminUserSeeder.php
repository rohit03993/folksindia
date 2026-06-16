<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'rohit03993@gmail.com');
        $password = (string) env('ADMIN_PASSWORD', 'Admin@2026');
        $name = (string) env('ADMIN_NAME', 'Super Admin');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'mobile' => env('ADMIN_MOBILE', '9876543210'),
                'is_active' => true,
            ],
        );

        $user->syncRoles([RoleName::SuperAdmin->value]);

        $this->command?->info("Super Admin ready: {$email}");
    }
}
