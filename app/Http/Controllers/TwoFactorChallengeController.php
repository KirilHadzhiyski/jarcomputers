<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Сесията за двуфакторна проверка е изтекла. Влезте отново.']);
        }

        return view('auth.two-factor-challenge', [
            'maskedEmail' => $this->maskEmail($user->email),
            'seo' => [
                'title' => 'Двуфакторна проверка',
                'description' => 'Потвърдете входа с код от приложението за удостоверяване.',
            ],
        ]);
    }

    public function store(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);
        $user = $this->pendingUser($request);

        if (! $user || ! $user->hasTwoFactorEnabled() || $user->isAccessBlocked()) {
            $request->session()->forget('two_factor_login');

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Сесията за двуфакторна проверка е невалидна. Влезте отново.']);
        }

        $step = $totp->verify(
            $user->two_factor_secret,
            $validated['code'],
            afterStep: $user->two_factor_last_used_step,
        );
        $usedRecoveryCode = false;

        if ($step === null) {
            $usedRecoveryCode = $totp->consumeRecoveryCode($user, $validated['code']);
        }

        if ($step === null && ! $usedRecoveryCode) {
            return back()->withErrors([
                'code' => 'Кодът е невалиден, вече използван или е изтекъл.',
            ]);
        }

        if ($step !== null) {
            $user->forceFill(['two_factor_last_used_step' => $step])->save();
        }

        $remember = (bool) data_get($request->session()->get('two_factor_login'), 'remember', false);
        $request->session()->forget('two_factor_login');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        AuditLog::query()->create([
            'user_id' => $user->id,
            'event' => $usedRecoveryCode ? 'auth.mfa.recovery_login' : 'auth.mfa.login',
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'response_status' => 302,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => [],
            'created_at' => now(),
        ]);

        return redirect()->intended(
            $user->isAdmin() ? route('admin.dashboard') : route('dashboard')
        );
    }

    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('two_factor_login');

        if (! is_array($pending)
            || ! isset($pending['user_id'], $pending['expires_at'])
            || (int) $pending['expires_at'] < now()->timestamp) {
            $request->session()->forget('two_factor_login');

            return null;
        }

        return User::query()->find($pending['user_id']);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($local, 0, 2);

        return $visible.str_repeat('•', max(2, mb_strlen($local) - 2)).'@'.$domain;
    }
}
