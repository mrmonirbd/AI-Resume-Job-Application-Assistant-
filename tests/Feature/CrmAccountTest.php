<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use App\Models\Concept;
use App\Models\Crm;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CmpApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CrmAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_remote_crm_login_creates_a_linked_local_account_and_subscription(): void
    {
        $concept = $this->crmConcept();

        Http::fake([
            'https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response([
                'status' => 'success',
                'data' => ['id' => 'web-1', 'customer_id' => 'customer-1'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-1.json' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 'customer-1',
                    'first_name' => 'Nusrat',
                    'last_name' => 'Jahan',
                    'phone' => '+8801700000000',
                    'address' => 'Road 1',
                    'city' => 'Dhaka',
                    'postcode' => '1205',
                ],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-1/subscriptions/active.json' => Http::response([
                'status' => 'success',
                'data' => [[
                    'id' => 'subscription-1',
                    'plan_id' => 'pro-1',
                    'status' => 'active',
                    'billing_period_end_date' => '2026-12-01 00:00:00',
                ]],
            ]),
        ]);

        $this->post(route('login'), [
            'email' => 'nusrat@example.com',
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'nusrat@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame($concept->id, $user->concept_id);
        $this->assertSame('customer-1', $user->crm_customer_id);
        $this->assertSame('subscription-1', $user->crm_subscription_id);
        $this->assertSame('Nusrat', $user->crm_profile['first_name']);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
        Http::assertSentCount(3);
    }

    public function test_profile_updates_are_sent_to_the_linked_crm_customer(): void
    {
        $concept = $this->crmConcept();
        $user = User::factory()->create([
            'concept_id' => $concept->id,
            'crm_customer_id' => 'customer-1',
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        Http::fake([
            'https://crm.example.test/api/s/v3/customers/customer-1.json' => Http::response([
                'status' => 'success',
                'data' => ['id' => 'customer-1'],
            ]),
        ]);

        $this->actingAs($user)->patch(route('profile.update'), [
            'first_name' => 'Nusrat',
            'last_name' => 'Jahan',
            'phone' => '+8801700000000',
            'address' => 'Road 1',
            'city' => 'Dhaka',
            'postcode' => '1205',
            'professional_title' => 'Credit Analyst',
            'location' => 'Dhaka',
            'career_goal' => 'Build a focused finance career.',
        ])->assertSessionHas('status', 'Profile updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nusrat Jahan',
            'professional_title' => 'Credit Analyst',
        ]);
        $this->assertSame('1205', $user->refresh()->crm_profile['postcode']);
        Http::assertSent(function ($request): bool {
            return $request->method() === 'PATCH'
                && $request->url() === 'https://crm.example.test/api/s/v3/customers/customer-1.json'
                && $request['first_name'] === 'Nusrat'
                && $request['last_name'] === 'Jahan';
        });
    }

    public function test_profile_photo_can_be_uploaded_and_used_by_the_profile_screen(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)->patch(route('profile.update'), [
            'first_name' => 'Nusrat',
            'last_name' => 'Jahan',
            'phone' => '+8801700000000',
            'address' => 'Road 1',
            'city' => 'Dhaka',
            'postcode' => '1205',
            'profile_photo' => UploadedFile::fake()->image('profile.png', 400, 400),
        ])->assertSessionHas('status', 'Profile updated.');

        $profilePhotoPath = $user->refresh()->profile_photo_path;

        $this->assertNotNull($profilePhotoPath);
        Storage::disk('public')->assertExists($profilePhotoPath);
        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('/storage/'.$profilePhotoPath, false)
            ->assertSee('Crop profile picture')
            ->assertSee('Use cropped photo')
            ->assertSee('profilePhotoCropper', false);
    }

    public function test_subscription_screen_loads_crm_invoices_and_can_cancel_the_linked_subscription(): void
    {
        $concept = $this->crmConcept();
        $user = User::factory()->create([
            'concept_id' => $concept->id,
            'crm_customer_id' => 'customer-1',
            'crm_subscription_id' => 'subscription-1',
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        Subscription::create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        Http::fake([
            'https://crm.example.test/api/s/v3/subscriptions/subscription-1.json' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 'subscription-1',
                    'name' => 'Pro',
                    'type' => 'recurring',
                    'status' => 'active',
                    'price' => '19.00',
                    'currency' => 'USD',
                    'interval' => '30',
                ],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-1/invoices.json' => Http::response([
                'status' => 'success',
                'data' => [['id' => 'invoice-1', 'status' => 'paid', 'total' => '$19.00']],
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('subscription.show'))
            ->assertOk()
            ->assertSee('Pro')
            ->assertSee('Terms &amp; Conditions', false)
            ->assertSee('Cancel Subscription');

        $this->actingAs($user)
            ->get(route('subscription.payment-history'))
            ->assertOk()
            ->assertSee('invoice-1');

        Http::fake([
            'https://crm.example.test/api/s/v3/subscriptions/subscription-1/cancel.json' => Http::response([
                'status' => 'success',
                'data' => 'Subscription cancelled',
            ]),
        ]);

        $this->actingAs($user)
            ->post(route('subscription.cancel'), ['confirmation' => 'cancel'])
            ->assertSessionHas('status', 'Your subscription has been cancelled.');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_legal_pages_are_public_and_load_policy_content_from_the_crm(): void
    {
        $this->crmConcept(['support_email' => 'support@example.test']);

        Http::fake([
            'https://crm.example.test/api/s/v3/website/tos/rendered.json?tos_type=privacy_policy&_locale=en' => Http::response([
                'data' => [
                    'html' => '<style>p { color: red; }</style><h2 style="font-family: serif">CRM privacy policy</h2><p>Your CRM policy content.</p><script>window.policy = true;</script>',
                ],
            ]),
        ]);

        $this->get(route('legal.show', 'privacy'))
            ->assertOk()
            ->assertSee('Privacy policy')
            ->assertSee('CRM privacy policy')
            ->assertSee('Your CRM policy content.')
            ->assertSee('support@example.test')
            ->assertDontSee('<style>', false)
            ->assertDontSee('font-family', false)
            ->assertDontSee('window.policy')
            ->assertSee('Terms &amp; Conditions', false)
            ->assertSee('Cancel Subscription');

        Http::assertSent(function ($request): bool {
            return $request->method() === 'GET'
                && $request->url() === 'https://crm.example.test/api/s/v3/website/tos/rendered.json?tos_type=privacy_policy&_locale=en';
        });
    }

    public function test_linked_local_login_respects_the_original_controller_success_response(): void
    {
        $concept = $this->crmConcept();
        $user = User::factory()->create([
            'concept_id' => $concept->id, 'crm_customer_id' => 'customer-1',
            'domain_name' => 'localhost', 'onboarding_completed_at' => now(),
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/customers/customer-1/subscriptions/active.json' => Http::response([
                'status' => 'success', 'data' => [],
            ]),
        ]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        Http::assertSentCount(1);
    }

    public function test_linked_local_login_is_rejected_when_the_subscription_has_ended(): void
    {
        $concept = $this->crmConcept();
        $user = User::factory()->create([
            'concept_id' => $concept->id, 'crm_customer_id' => 'customer-1',
            'domain_name' => 'localhost', 'onboarding_completed_at' => now(),
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/customers/customer-1/subscriptions/active.json' => Http::response([
                'status' => 'error', 'data' => [],
            ]),
        ]);

        $this->withSession(['private_draft' => 'Draft'])->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('error');

        $this->assertGuest();
        Http::assertSentCount(1);
    }

    public function test_linked_local_login_handles_cmp_connection_failures(): void
    {
        $concept = $this->crmConcept();
        $user = User::factory()->create([
            'concept_id' => $concept->id, 'crm_customer_id' => 'customer-1',
            'domain_name' => 'localhost', 'onboarding_completed_at' => now(),
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/customers/customer-1/subscriptions/active.json' => Http::failedConnection(),
        ]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        Http::assertSentCount(1);
    }

    public function test_login_cannot_reassign_an_existing_account_from_another_domain(): void
    {
        $this->crmConcept();
        $user = User::factory()->create(['domain_name' => 'another.example.test', 'onboarding_completed_at' => now()]);
        Http::preventStrayRequests();
        Http::fake(['https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response(['status' => 'error'])]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('error');

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'domain_name' => 'another.example.test', 'crm_customer_id' => null]);
        $this->assertDatabaseCount('subscriptions', 0);
        Http::assertSentCount(1);
    }

    public function test_native_cmp_subscription_response_does_not_store_plain_passwords(): void
    {
        $this->crmConcept();
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'web-2', 'customer_id' => 'customer-2'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-2.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'customer-2', 'first_name' => 'John', 'last_name' => 'Doe'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-2/subscriptions/active.json' => Http::response([
                'status' => 'success', 'data' => [['id' => 'subscription-2', 'plan_id' => 'remote-pro', 'status' => 'active', 'billing_period_end_date' => null]],
            ]),
        ]);

        $this->post(route('login'), ['email' => 'john@example.test', 'password' => 'remote-password'])
            ->assertRedirect(route('dashboard'));

        $user = User::where('email', 'john@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('customer-2', $user->crm_customer_id);
        $this->assertSame('subscription-2', $user->crm_subscription_id);
        $this->assertArrayNotHasKey('plain_password', $user->getAttributes());
        $this->assertTrue(Hash::check('remote-password', $user->password));
        $this->assertDatabaseHas('subscriptions', ['user_id' => $user->id, 'status' => 'active', 'plan_id' => null]);
        Http::assertSentCount(3);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_remote_login_updates_an_existing_local_account_without_creating_a_duplicate(bool $hasSubscription): void
    {
        $concept = $this->crmConcept();
        $user = User::factory()->create(['email' => 'returning@example.test', 'domain_name' => 'localhost', 'concept_id' => $concept->id, 'crm_customer_id' => 'customer-3']);
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'web-3', 'customer_id' => 'customer-3'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-3.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'customer-3', 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-3/subscriptions/active.json' => Http::response([
                'status' => 'success', 'data' => $hasSubscription ? [['id' => 'subscription-3', 'status' => 'active']] : [],
            ]),
        ]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'changed-remote-password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'crm_customer_id' => 'customer-3', 'name' => 'Jane Doe']);
        Http::assertSentCount(3);
    }

    public function test_login_controller_keeps_the_original_cmp_api_instance(): void
    {
        $concept = $this->crmConcept();
        $controller = app(LoginController::class);
        $controller->callAction('showLoginForm', []);
        $client = (new \ReflectionProperty($controller, 'cmp'))->getValue($controller);

        $this->assertSame(CmpApi::class, $client::class);
    }

    public function test_unsupported_cmp_subscription_shape_returns_a_validation_error(): void
    {
        $this->crmConcept();
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'web-4', 'customer_id' => 'customer-4'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-4.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'customer-4', 'first_name' => 'John', 'last_name' => 'Doe'],
            ]),
            'https://crm.example.test/api/s/v3/customers/customer-4/subscriptions/active.json' => Http::response([
                'status' => 'success', 'data' => ['id' => 'subscription-4', 'status' => 'active'],
            ]),
        ]);

        $this->post(route('login'), ['email' => 'unsupported@example.test', 'password' => 'remote-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        Http::assertSentCount(3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function crmConcept(array $attributes = []): Concept
    {
        $crm = Crm::create([
            'name' => 'Test CRM',
            'api_url' => 'https://crm.example.test',
            'is_enabled' => true,
        ]);

        return Concept::create(array_merge([
            'name' => 'Resume CRM',
            'domain_name' => 'localhost',
            'crm_id' => $crm->id,
            'crm_api_key' => 'crm-key',
            'is_default' => true,
            'is_enabled' => true,
        ], $attributes));
    }
}
