<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_authenticate_via_filament_login(): void
    {
        Role::query()->firstOrCreate(['name' => RoleName::SuperAdmin->value, 'guard_name' => 'web']);

        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $user->assignRole(RoleName::SuperAdmin->value);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm([
                'email' => 'admin@test.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_without_role_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'nostaff@test.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm([
                'email' => 'nostaff@test.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasErrors(['data.email']);
    }
}
