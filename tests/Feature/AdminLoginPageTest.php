<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The branded sign-in screen at /admin/login.
 *
 * The interesting cases are the refusals. Putting a login form on an admin URL
 * is only safe because Filament authenticates with
 * attemptWhen(... canAccessPanel), so a customer with correct credentials is
 * denied a session outright rather than signed in and shown a 403.
 */
class AdminLoginPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Testing the Livewire component directly skips the middleware that
        // would normally set the current panel.
        \Filament\Facades\Filament::setCurrentPanel('admin');
    }

    public function test_the_login_screen_exists_and_is_branded(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        // Brand name and logo come from the panel config.
        $response->assertSee('Cardyshub', false);
        $response->assertSee('brand/full-colored.svg', false);
    }

    public function test_an_admin_can_sign_in_there(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('correct-horse-battery'),
            'is_admin' => true,
        ]);

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'correct-horse-battery'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_customer_with_correct_credentials_is_refused(): void
    {
        // The whole reason this page is safe to expose. A non-admin must not
        // get a session at all, not merely a 403 after signing in.
        $customer = User::factory()->create([
            'email' => 'shopper@example.test',
            'password' => Hash::make('correct-horse-battery'),
            'is_admin' => false,
        ]);

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm(['email' => $customer->email, 'password' => 'correct-horse-battery'])
            ->call('authenticate')
            ->assertHasFormErrors();

        $this->assertGuest();
    }

    public function test_a_wrong_password_is_refused(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('correct-horse-battery'),
            'is_admin' => true,
        ]);

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm(['email' => 'admin@example.test', 'password' => 'wrong'])
            ->call('authenticate')
            ->assertHasFormErrors();

        $this->assertGuest();
    }

    public function test_a_signed_out_visitor_is_sent_to_the_admin_login_not_the_shop(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_a_customer_who_is_already_signed_in_still_cannot_open_the_panel(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)->get('/admin')->assertForbidden();
    }
}
