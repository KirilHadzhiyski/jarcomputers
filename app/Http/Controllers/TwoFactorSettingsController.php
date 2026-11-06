<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSettingsController extends Controller
{
    public function show(Request $request, TotpService $totp): View
    {
        $user = $request->user();

        return view('dashboard.security', [
            'formattedSecret' => $user->two_factor_secret && ! $user->hasTwoFactorEnabled()
                ? $totp->formatSecret($user->two_factor_secret)
                : null,
            'otpauthUri' => $user->two_factor_secret && ! $user->hasTwoFactorEnabled()
                ? $totp->otpauthUri($user)
                : null,
            'recoveryCodes' => session('two_factor_recovery_codes', []),
            'seo' => [
                'title' => 'Сигурност на профила',
                'description' => 'Управление на двуфакторната защита на профила.',
            ],
        ]);
    }

    public function prepare(Request $request, TotpService $totp): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        $plainRecoveryCodes = $totp->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $totp->generateSecret(),
            'two_factor_recovery_codes' => $totp->hashRecoveryCodes($plainRecoveryCodes),
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
        ])->save();
        $request->session()->put('two_factor_pending_recovery_codes', $plainRecoveryCodes);
        $this->audit($request, $user, 'auth.mfa.prepared');

        return redirect()
            ->route('security.show')
            ->with('status', 'Добавете показания ключ в приложението си и въведете генерирания 6-цифрен код.');
    }

    public function confirm(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $user = $request->user();

        if (! $user->two_factor_secret || $user->hasTwoFactorEnabled()) {
            return redirect()->route('security.show');
        }

        $step = $totp->verify($user->two_factor_secret, $validated['code']);

        if ($step === null) {
            return back()->withErrors(['code' => 'Кодът не съвпада. Проверете часа на устройството и опитайте отново.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_last_used_step' => $step,
        ])->save();
        $recoveryCodes = $request->session()->pull('two_factor_pending_recovery_codes', []);
        $this->audit($request, $user, 'auth.mfa.enabled');

        return redirect()
            ->route('security.show')
            ->with('status', 'Двуфакторната защита е активирана.')
            ->with('two_factor_recovery_codes', $recoveryCodes);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_last_used_step' => null,
            ])->save();
        }

        $request->session()->forget('two_factor_pending_recovery_codes');

        return redirect()
            ->route('security.show')
            ->with('status', 'Незавършената настройка беше отменена.');
    }

    public function regenerateRecoveryCodes(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:32'],
        ]);
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled() || ! $this->verifyCurrentCode($user, $validated['code'], $totp)) {
            return back()->withErrors(['code' => 'Кодът за потвърждение е невалиден.']);
        }

        $plainRecoveryCodes = $totp->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => $totp->hashRecoveryCodes($plainRecoveryCodes),
        ])->save();
        $this->audit($request, $user, 'auth.mfa.recovery_codes_regenerated');

        return redirect()
            ->route('security.show')
            ->with('status', 'Създадени са нови резервни кодове. Старите вече не работят.')
            ->with('two_factor_recovery_codes', $plainRecoveryCodes);
    }

    public function destroy(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:32'],
        ]);
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled() || ! $this->verifyCurrentCode($user, $validated['code'], $totp)) {
            return back()->withErrors(['code' => 'Кодът за потвърждение е невалиден.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
        ])->save();
        $this->audit($request, $user, 'auth.mfa.disabled');

        return redirect()
            ->route('security.show')
            ->with('status', 'Двуфакторната защита е изключена.');
    }

    private function verifyCurrentCode(User $user, string $code, TotpService $totp): bool
    {
        $step = $totp->verify(
            $user->two_factor_secret,
            $code,
            afterStep: $user->two_factor_last_used_step,
        );

        if ($step !== null) {
            $user->forceFill(['two_factor_last_used_step' => $step])->save();

            return true;
        }

        return $totp->consumeRecoveryCode($user, $code);
    }

    private function audit(Request $request, User $user, string $event): void
    {
        AuditLog::query()->create([
            'user_id' => $user->id,
            'event' => $event,
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'response_status' => 302,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => [],
            'created_at' => now(),
        ]);
    }
}
