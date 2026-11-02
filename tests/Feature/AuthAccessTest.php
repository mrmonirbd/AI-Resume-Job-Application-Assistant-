<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_registration_routes_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Demo',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
    }

    public function test_seeded_demo_user_can_log_in_without_onboarding(): void
    {
        $this->seed();

        $this->post(route('login'), [
            'email' => 'demo@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'demo@example.com')->first());
    }

    public function test_login_screen_renders_without_a_landing_route(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Welcome back');
    }

    public function test_authenticated_users_see_the_dashboard_at_the_root_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk()->assertViewIs('app.dashboard');
        $this->get('/dashboard')->assertRedirect(route('dashboard'));
        $this->get(route('login'))->assertRedirect(route('dashboard'));
    }

    public function test_logout_invalidates_the_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['private_draft' => 'Unsaved draft'])
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing('private_draft');

        $this->assertGuest();
        $this->get(route('login'))->assertOk();
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_deleting_an_account_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertModelMissing($user);
    }
}
