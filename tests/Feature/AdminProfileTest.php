<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['password' => 'Old-password-1']);
    }

    public function test_profile_page_requires_authentication(): void
    {
        $this->get('/admin/profile')->assertRedirect('/admin/login');
    }

    public function test_profile_page_renders_with_two_factor_section(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee('Profil', false);
    }

    public function test_admin_can_change_name_without_touching_password(): void
    {
        $user = $this->admin();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->fillForm(['name' => 'Koordinator Baru', 'email' => $user->email])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('Koordinator Baru', $user->name);
        $this->assertTrue(Hash::check('Old-password-1', $user->password));
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = $this->admin();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'New-password-123',
                'passwordConfirmation' => 'New-password-123',
                'currentPassword' => 'wrong-current-password',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $this->assertTrue(Hash::check('Old-password-1', $user->fresh()->password));
    }

    public function test_password_change_requires_matching_confirmation(): void
    {
        $user = $this->admin();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'New-password-123',
                'passwordConfirmation' => 'something-else-entirely',
                'currentPassword' => 'Old-password-1',
            ])
            ->call('save')
            ->assertHasFormErrors(['password']);
    }

    public function test_password_can_be_changed_with_current_password(): void
    {
        $user = $this->admin();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'New-password-123',
                'passwordConfirmation' => 'New-password-123',
                'currentPassword' => 'Old-password-1',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('New-password-123', $user->fresh()->password));
    }
}
