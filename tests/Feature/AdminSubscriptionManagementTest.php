<?php

namespace Tests\Feature;

use App\Models\ClientSubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\UsageRecord;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_plan_subscription_payment_and_usage(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'user']);

        $planResponse = $this->actingAs($admin)->post('/admin/subscription-plans', [
            'name' => 'Growth',
            'code' => 'growth',
            'description' => 'For growing catalog operations.',
            'monthly_price' => 149,
            'currency_code' => 'EUR',
            'workflow_limit' => 10,
            'records_per_month' => 50000,
            'ai_budget' => 40,
            'features' => "Price monitoring\nAI classification",
            'is_active' => '1',
        ]);

        $plan = SubscriptionPlan::query()->firstOrFail();
        $planResponse->assertRedirect(route('admin.subscription-plans.edit', $plan));
        $this->assertSame(['Price monitoring', 'AI classification'], $plan->features);

        $subscriptionResponse = $this->actingAs($admin)->post('/admin/client-subscriptions', [
            'user_id' => $client->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->startOfDay()->toDateTimeString(),
            'renews_at' => now()->addMonth()->startOfDay()->toDateTimeString(),
            'external_provider' => 'manual',
        ]);

        $subscription = ClientSubscription::query()->firstOrFail();
        $subscriptionResponse->assertRedirect(route('admin.client-subscriptions.edit', $subscription));

        $paymentResponse = $this->actingAs($admin)->post('/admin/subscription-payments', [
            'client_subscription_id' => $subscription->id,
            'amount' => 149,
            'currency_code' => 'EUR',
            'status' => 'paid',
            'provider' => 'manual',
            'external_reference' => 'PAY-TEST-001',
            'billed_at' => now()->toDateTimeString(),
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $payment = SubscriptionPayment::query()->firstOrFail();
        $paymentResponse->assertRedirect(route('admin.subscription-payments.edit', $payment));
        $this->assertSame($client->id, $payment->user_id);

        $this->actingAs($admin)->post('/admin/usage-records', [
            'user_id' => $client->id,
            'service_type' => 'ai',
            'provider' => 'OpenAI',
            'model' => 'classification-model',
            'quantity' => 2500,
            'unit' => 'token',
            'cost' => 3.75,
            'currency_code' => 'EUR',
            'occurred_at' => now()->toDateTimeString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('usage_records', [
            'user_id' => $client->id,
            'provider' => 'OpenAI',
            'cost' => 3.75,
        ]);

        $this->actingAs($admin)
            ->get('/admin/subscriptions')
            ->assertOk()
            ->assertSee('149.00 EUR')
            ->assertSee('3.75 EUR')
            ->assertSee($client->name);
    }

    public function test_subscription_pages_are_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        foreach (['/admin/subscriptions', '/admin/subscription-plans', '/admin/client-subscriptions', '/admin/subscription-payments', '/admin/usage-records'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
            $this->actingAs($user)->get($path)->assertForbidden();
        }
    }

    public function test_suspended_or_deactivated_account_cannot_access_the_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create([
            'role' => 'user',
            'password' => 'Password123',
        ]);

        $this->actingAs($admin)->put("/admin/users/{$client->id}", [
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'preferred_contact_channel' => 'email',
            'role' => 'user',
            'account_status' => 'suspended',
            'suspended_until' => now()->addDay()->toDateTimeString(),
            'account_notes' => 'Billing review',
        ])->assertRedirect(route('admin.users.edit', $client));

        $this->assertTrue($client->fresh()->isAccessBlocked());
        $this->assertSame('Billing review', $client->fresh()->admin_metadata['notes']);

        auth()->logout();

        $this->post('/login', [
            'email' => $client->email,
            'password' => 'Password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($client->fresh())
            ->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_expired_temporary_suspension_allows_login_again(): void
    {
        $client = User::factory()->create([
            'role' => 'user',
            'password' => 'Password123',
            'account_status' => 'suspended',
            'suspended_until' => now()->subMinute(),
        ]);

        $this->post('/login', [
            'email' => $client->email,
            'password' => 'Password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($client);
    }

    public function test_usage_summary_counts_only_current_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        UsageRecord::query()->create([
            'service_type' => 'api',
            'provider' => 'Current provider',
            'quantity' => 10,
            'unit' => 'request',
            'cost' => 5,
            'currency_code' => 'EUR',
            'occurred_at' => now(),
        ]);
        UsageRecord::query()->create([
            'service_type' => 'api',
            'provider' => 'Old provider',
            'quantity' => 10,
            'unit' => 'request',
            'cost' => 50,
            'currency_code' => 'EUR',
            'occurred_at' => now()->subMonths(2),
        ]);
        UsageRecord::query()->create([
            'service_type' => 'api',
            'provider' => 'Bulgarian provider',
            'quantity' => 3,
            'unit' => 'request',
            'cost' => 9,
            'currency_code' => 'BGN',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)->get('/admin/subscriptions')
            ->assertOk()
            ->assertSee('5.00 EUR')
            ->assertSee('9.00 BGN')
            ->assertDontSee('14.00 EUR')
            ->assertDontSee('55.00 EUR');

        $this->actingAs($admin)->get('/admin/usage-records')
            ->assertOk()
            ->assertSee('5.0000 EUR')
            ->assertSee('9.0000 BGN')
            ->assertDontSee('14.0000 EUR');
    }

    public function test_client_plan_limits_new_workflows_and_is_visible_in_client_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'user']);
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
            'monthly_price' => 49,
            'currency_code' => 'EUR',
            'workflow_limit' => 1,
            'records_per_month' => 1000,
            'ai_budget' => 10,
            'is_active' => true,
        ]);
        ClientSubscription::query()->create([
            'user_id' => $client->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'renews_at' => now()->addMonth(),
        ]);
        Workflow::query()->create([
            'client_user_id' => $client->id,
            'name' => 'Existing workflow',
            'slug' => 'existing-workflow',
            'status' => 'active',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'approval_mode' => 'review',
        ]);

        $this->actingAs($admin)->post('/admin/workflows', [
            'client_user_id' => $client->id,
            'name' => 'Second workflow',
            'status' => 'draft',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'approval_mode' => 'review',
        ])->assertSessionHasErrors('client_user_id');

        $this->assertDatabaseCount('workflows', 1);

        $this->actingAs($client)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('1 / 1');
    }
}
