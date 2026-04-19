<?php

namespace Tests\Feature;

use App\Mail\AccountVerificationCodeMail;
use App\Mail\TicketCustomerUpdateMail;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTicketAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_must_verify_email_code_before_login(): void
    {
        Mail::fake();

        $registerResponse = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'phone' => '+359888111222',
            'preferred_contact_channel' => 'email',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $registerResponse->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.com',
            'role' => 'user',
            'phone' => '+359888111222',
            'email_verified_at' => null,
        ]);

        $sentCode = null;

        Mail::assertSent(AccountVerificationCodeMail::class, function (AccountVerificationCodeMail $mail) use (&$sentCode): bool {
            $sentCode = $mail->code;

            return $mail->hasTo('new-user@example.com');
        });

        $verifyResponse = $this->post('/verify-account', [
            'email' => 'new-user@example.com',
            'code' => $sentCode,
        ]);

        $verifyResponse->assertRedirect(route('login'));
        $this->assertNotNull(User::query()->where('email', 'new-user@example.com')->value('email_verified_at'));

        $loginResponse = $this->post('/login', [
            'email' => 'new-user@example.com',
            'password' => 'Password123',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_unverified_user_is_redirected_to_code_confirmation_when_trying_to_login(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create([
            'phone' => '+359899000111',
            'preferred_contact_channel' => 'email',
            'password' => 'Password123',
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();

        Mail::assertSent(AccountVerificationCodeMail::class, fn (AccountVerificationCodeMail $mail): bool => $mail->hasTo($user->email));
    }

    public function test_registering_again_with_unverified_email_updates_profile_and_resends_code(): void
    {
        Mail::fake();

        User::factory()->unverified()->create([
            'name' => 'Old Name',
            'email' => 'pending@example.com',
            'phone' => '+359800000000',
            'preferred_contact_channel' => 'email',
            'password' => 'Password123',
        ]);

        $response = $this->post('/register', [
            'name' => 'Updated Pending User',
            'email' => 'pending@example.com',
            'phone' => '+359811111111',
            'preferred_contact_channel' => 'whatsapp',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'name' => 'Updated Pending User',
            'phone' => '+359811111111',
            'preferred_contact_channel' => 'whatsapp',
            'email_verified_at' => null,
        ]);
        $this->assertTrue(Hash::check('NewPassword123', User::query()->where('email', 'pending@example.com')->firstOrFail()->password));

        Mail::assertSent(AccountVerificationCodeMail::class, fn (AccountVerificationCodeMail $mail): bool => $mail->hasTo('pending@example.com'));
    }

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'Ако има профил с този имейл, изпратихме линк за смяна на паролата.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_from_email_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'user',
            'password' => 'Password123',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $token = null;

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))->assertOk();

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Паролата беше сменена успешно. Можете да влезете с новата парола.');

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'NewPassword123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_password_reset_does_not_bypass_email_verification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'role' => 'user',
            'password' => 'Password123',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $token = null;

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'FreshPassword123',
            'password_confirmation' => 'FreshPassword123',
        ])->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'FreshPassword123',
        ])->assertRedirect(route('verification.notice'));

        $this->assertGuest();
    }

    public function test_authenticated_user_can_update_profile_and_create_a_ticket(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $profileResponse = $this->actingAs($user)->put('/dashboard/profile', [
            'name' => 'Updated User',
            'phone' => '+359877000999',
            'preferred_contact_channel' => 'viber',
        ]);

        $profileResponse->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'phone' => '+359877000999',
            'preferred_contact_channel' => 'viber',
        ]);

        $ticketResponse = $this->actingAs($user)->post('/tickets', [
            'subject' => 'Broken display',
            'device_model' => 'iPhone 14 Pro',
            'category' => 'repair',
            'priority' => 'high',
            'description' => 'The display is cracked and touch response is inconsistent.',
        ]);

        $ticket = Ticket::query()->firstOrFail();

        $ticketResponse->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'subject' => 'Broken display',
            'priority' => 'high',
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('ticket_updates', [
            'ticket_id' => $ticket->id,
            'new_status' => 'open',
            'is_internal' => false,
        ]);
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_launch_readiness_panel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Launch readiness')
            ->assertSee('Домейн и APP_URL')
            ->assertSee('Email доставка');
    }

    public function test_admin_can_update_ticket_and_notify_customer(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $customer = User::factory()->create([
            'role' => 'user',
        ]);

        $ticket = Ticket::query()->create([
            'user_id' => $customer->id,
            'subject' => 'Battery issue',
            'device_model' => 'iPhone 13',
            'category' => 'repair',
            'priority' => 'normal',
            'status' => 'open',
            'description' => 'Battery health drops quickly.',
            'admin_notes' => 'Initial diagnostics pending.',
        ]);

        $response = $this->actingAs($admin)->put("/admin/tickets/{$ticket->id}", [
            'user_id' => $customer->id,
            'subject' => 'Battery issue',
            'device_model' => 'iPhone 13',
            'category' => 'repair',
            'priority' => 'urgent',
            'status' => 'ready_for_pickup',
            'description' => 'Battery replaced successfully.',
            'admin_notes' => 'Ready for pickup.',
            'customer_message' => 'Устройството е готово за взимане от сервиза.',
            'notify_customer' => '1',
        ]);

        $response->assertRedirect(route('admin.tickets.edit', $ticket));
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'priority' => 'urgent',
            'status' => 'ready_for_pickup',
            'admin_notes' => 'Ready for pickup.',
        ]);
        $this->assertDatabaseHas('ticket_updates', [
            'ticket_id' => $ticket->id,
            'old_status' => 'open',
            'new_status' => 'ready_for_pickup',
            'message' => 'Устройството е готово за взимане от сервиза.',
        ]);

        Mail::assertSent(TicketCustomerUpdateMail::class, fn (TicketCustomerUpdateMail $mail): bool => $mail->hasTo($customer->email));
    }

    public function test_admin_can_create_update_and_delete_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $createResponse = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Support Agent',
            'email' => 'support@example.com',
            'phone' => '+359888777666',
            'preferred_contact_channel' => 'phone',
            'role' => 'user',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $createdUser = User::query()->where('email', 'support@example.com')->firstOrFail();

        $createResponse->assertRedirect(route('admin.users.edit', $createdUser));
        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'role' => 'user',
            'phone' => '+359888777666',
            'preferred_contact_channel' => 'phone',
        ]);

        $updateResponse = $this->actingAs($admin)->put("/admin/users/{$createdUser->id}", [
            'name' => 'Senior Support Agent',
            'email' => 'support@example.com',
            'phone' => '+359888000000',
            'preferred_contact_channel' => 'whatsapp',
            'role' => 'admin',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $updateResponse->assertRedirect(route('admin.users.edit', $createdUser));
        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'name' => 'Senior Support Agent',
            'role' => 'admin',
            'phone' => '+359888000000',
            'preferred_contact_channel' => 'whatsapp',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete("/admin/users/{$createdUser->id}");

        $deleteResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $createdUser->id,
        ]);
    }
}
