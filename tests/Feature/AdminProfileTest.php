<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Changing the admin password from inside /admin.
 *
 * This matters more than it looks: the live server has no shell, so without a
 * page in the panel the only way to rotate the admin password would be to
 * hand-write a bcrypt hash into the database through phpMyAdmin.
 */
class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Testing the Livewire component directly skips the panel middleware
        // that would normally set the current panel, so set it here. The panel
        // is deliberately not marked default() in the app itself.
        \Filament\Facades\Filament::setCurrentPanel('admin');
    }

    protected function admin(string $password = 'old-password-123'): User
    {
        return User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);
    }

    public function test_an_admin_can_reach_the_profile_page(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/profile')
            ->assertOk();
    }

    public function test_a_guest_cannot_reach_it(): void
    {
        $this->get('/admin/profile')->assertRedirect();
    }

    public function test_the_password_can_actually_be_changed(): void
    {
        $admin = $this->admin('old-password-123');

        Livewire::actingAs($admin)
            ->test(\Filament\Auth\Pages\EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'password' => 'a-much-better-password-99',
                'passwordConfirmation' => 'a-much-better-password-99',
                'currentPassword' => 'old-password-123',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $admin->refresh();

        $this->assertTrue(Hash::check('a-much-better-password-99', $admin->password));
        $this->assertFalse(Hash::check('old-password-123', $admin->password));
    }

    public function test_the_current_password_is_required_to_change_it(): void
    {
        // Otherwise an unattended session could be used to lock the owner out.
        $admin = $this->admin('old-password-123');

        Livewire::actingAs($admin)
            ->test(\Filament\Auth\Pages\EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'password' => 'attacker-chosen-password',
                'passwordConfirmation' => 'attacker-chosen-password',
                'currentPassword' => 'not-the-real-one',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $admin->refresh();
        $this->assertTrue(Hash::check('old-password-123', $admin->password));
    }

    public function test_leaving_the_password_blank_keeps_the_existing_one(): void
    {
        $admin = $this->admin('old-password-123');

        Livewire::actingAs($admin)
            ->test(\Filament\Auth\Pages\EditProfile::class)
            ->fillForm([
                'name' => 'Renamed Admin',
                'email' => $admin->email,
                'password' => '',
                'passwordConfirmation' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $admin->refresh();

        $this->assertSame('Renamed Admin', $admin->name);
        $this->assertTrue(Hash::check('old-password-123', $admin->password));
    }
}
