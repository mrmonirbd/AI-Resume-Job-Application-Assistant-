<?php

namespace Tests\Feature;

use App\Models\Concept;
use App\Models\Crm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_account_password_is_changed(): void
    {
        $user = User::factory()->create();
        Http::preventStrayRequests();
        $this->actingAs($user)->put(route('profile.password'), $this->payload())
            ->assertSessionHas('status', 'Password changed.');
        $this->assertTrue(Hash::check('new-password-123', $user->refresh()->password));
        Http::assertNothingSent();
    }

    public function test_linked_account_uses_original_cmp_api_before_saving_password(): void
    {
        $user = $this->linkedUser();
        Http::preventStrayRequests();
        Http::fake(['https://crm.example.test/api/s/v3/users/web-1.json' => Http::response(['status' => 'success'])]);
        $this->actingAs($user)->put(route('profile.password'), $this->payload())
            ->assertSessionHas('status', 'Password changed.');
        $this->assertTrue(Hash::check('new-password-123', $user->refresh()->password));
        Http::assertSent(fn ($request) => $request->method() === 'PATCH' && $request['password'] === 'new-password-123');
        Http::assertSentCount(1);
    }

    public function test_failed_crm_update_preserves_the_existing_password(): void
    {
        $user = $this->linkedUser();
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['status' => 'error'], 500)]);
        $this->actingAs($user)->put(route('profile.password'), $this->payload())->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password', $user->refresh()->password));
        Http::assertSentCount(1);
    }

    public function test_wrong_current_password_does_not_call_crm(): void
    {
        $user = $this->linkedUser();
        Http::preventStrayRequests();
        $this->actingAs($user)->put(route('profile.password'), [...$this->payload(), 'current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');
        Http::assertNothingSent();
        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    private function linkedUser(): User
    {
        $crm = Crm::create(['name' => 'CRM', 'api_url' => 'https://crm.example.test', 'is_enabled' => true]);
        $concept = Concept::create(['name' => 'Resume', 'domain_name' => 'localhost', 'crm_id' => $crm->id, 'crm_api_key' => 'test-key']);

        return User::factory()->create(['concept_id' => $concept->id, 'crm_customer_id' => 'customer-1', 'crm_web_user_id' => 'web-1']);
    }

    private function payload(): array
    {
        return ['current_password' => 'password', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];
    }
}
