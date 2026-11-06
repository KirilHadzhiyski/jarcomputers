<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_totp_implementation_matches_the_rfc_6238_sha1_vector(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('287082', app(TotpService::class)->code($secret, 59));
        $this->assertSame(1, app(TotpService::class)->verify($secret, '287082', 59));
    }

    public function test_password_login_requires_second_factor_when_enabled(): void
    {
        $this->freezeTime();
        $user = $this->mfaUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();

        $code = app(TotpService::class)->code($user->two_factor_secret);
        $this->post('/two-factor-challenge', ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'auth.mfa.login',
        ]);
    }

    public function test_totp_code_cannot_be_reused_for_a_second_login(): void
    {
        $this->freezeTime();
        $user = $this->mfaUser();
        $code = app(TotpService::class)->code($user->two_factor_secret);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123']);
        $this->post('/two-factor-challenge', ['code' => $code])->assertRedirect(route('dashboard'));
        $this->post('/logout');

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123']);
        $this->post('/two-factor-challenge', ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_recovery_code_logs_in_once_and_is_consumed(): void
    {
        $this->freezeTime();
        $user = $this->mfaUser(['JAR1-TEST-CODE']);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123']);
        $this->post('/two-factor-challenge', ['code' => 'jar1 test code'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);
        $this->assertDatabaseHas('audit_logs', ['event' => 'auth.mfa.recovery_login']);
    }

    public function test_user_can_prepare_and_confirm_two_factor_authentication(): void
    {
        $this->freezeTime();
        $user = User::factory()->create([
            'password' => 'Password123',
        ]);

        $this->actingAs($user)
            ->post('/dashboard/security/two-factor', ['password' => 'Password123'])
            ->assertRedirect(route('security.show'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->hasTwoFactorEnabled());

        $code = app(TotpService::class)->code($user->two_factor_secret);
        $this->post('/dashboard/security/two-factor/confirm', ['code' => $code])
            ->assertRedirect(route('security.show'))
            ->assertSessionHas('two_factor_recovery_codes');

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertSame(8, count($user->fresh()->two_factor_recovery_codes));
        $this->assertDatabaseHas('audit_logs', ['event' => 'auth.mfa.enabled']);
    }

    public function test_invalid_password_cannot_prepare_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'password' => 'Password123',
        ]);

        $this->actingAs($user)
            ->post('/dashboard/security/two-factor', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_admin_panel_can_require_mfa_without_blocking_security_setup(): void
    {
        config()->set('security.mfa.enforce_admins', true);
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'Password123',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertRedirect(route('security.show'));

        $this->get('/dashboard/security')
            ->assertOk()
            ->assertSee('Двуфакторна защита');

        $enabledAdmin = $this->mfaUser();
        $enabledAdmin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($enabledAdmin)
            ->get('/admin')
            ->assertOk();
    }

    private function mfaUser(array $recoveryCodes = []): User
    {
        $totp = app(TotpService::class);

        return User::factory()->create([
            'password' => 'Password123',
            'two_factor_secret' => $totp->generateSecret(),
            'two_factor_recovery_codes' => array_map(
                fn (string $code): string => Hash::make(strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code))),
                $recoveryCodes,
            ),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
