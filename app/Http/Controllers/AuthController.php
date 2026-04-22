<?php

namespace App\Http\Controllers;

use App\Mail\AccountVerificationCodeMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', [
            'seo' => [
                'title' => 'Вход',
                'description' => 'Влезте в профила си, за да следите поръчките и сервизните билети.',
            ],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'email' => 'Невалиден имейл или парола.',
                ])
                ->onlyInput('email');
        }

        if (! $user->hasVerifiedEmail()) {
            $this->sendVerificationCode($user);
            $request->session()->put('verification_email', $user->email);

            return redirect()
                ->route('verification.notice')
                ->with('status', 'Профилът още не е потвърден. Изпратихме нов код на вашия имейл.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(
            $request->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')
        );
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'contactChannels' => User::CONTACT_CHANNEL_LABELS,
            'seo' => [
                'title' => 'Регистрация',
                'description' => 'Създайте профил, за да следите поръчките и сервизните билети си.',
            ],
        ]);
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password', [
            'seo' => [
                'title' => 'Забравена парола',
                'description' => 'Поискайте линк за смяна на паролата на вашия профил.',
            ],
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'preferred_contact_channel' => ['required', 'in:email,phone'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $existingUser = User::query()->where('email', $validated['email'])->first();

        if ($existingUser?->hasVerifiedEmail() || $existingUser?->isAdmin()) {
            return back()
                ->withErrors([
                    'email' => 'Вече има активен профил с този имейл. Влезте в системата или използвайте друг адрес.',
                ])
                ->onlyInput('name', 'email', 'phone', 'preferred_contact_channel');
        }

        if ($existingUser) {
            $existingUser->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'preferred_contact_channel' => $validated['preferred_contact_channel'],
                'password' => $validated['password'],
                'role' => 'user',
                'email_verified_at' => null,
                'email_verification_code' => null,
                'email_verification_expires_at' => null,
            ]);

            $user = $existingUser->fresh();
            $statusMessage = 'Открихме непотвърден профил с този имейл. Обновихме данните и изпратихме нов код.';
        } else {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'preferred_contact_channel' => $validated['preferred_contact_channel'],
                'password' => $validated['password'],
                'role' => 'user',
            ]);

            $statusMessage = 'Изпратихме 6-цифрен код за потвърждение на посочения имейл.';
        }

        $this->sendVerificationCode($user);
        $request->session()->put('verification_email', $user->email);

        return redirect()
            ->route('verification.notice')
            ->with('status', $statusMessage);
    }

    public function showVerification(Request $request): View
    {
        return view('auth.verify-code', [
            'verificationEmail' => old('email', $request->query('email', $request->session()->get('verification_email'))),
            'seo' => [
                'title' => 'Потвърждение на профил',
                'description' => 'Въведете кода от имейла, за да активирате профила си.',
            ],
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        return back()->with('status', 'Ако има профил с този имейл, изпратихме линк за смяна на паролата.');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => old('email', $request->query('email', '')),
            'seo' => [
                'title' => 'Смяна на парола',
                'description' => 'Въведете нова парола за вашия профил.',
            ],
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors([
                    'email' => 'Линкът за смяна на паролата е невалиден или е изтекъл.',
                ])
                ->onlyInput('email');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Паролата беше сменена успешно. Можете да влезете с новата парола.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'email' => 'Не открихме профил с този имейл.',
                ])
                ->withInput();
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('status', 'Профилът вече е потвърден. Можете да влезете.');
        }

        if (! $user->verificationCodeIsValid($validated['code'])) {
            return back()
                ->withErrors([
                    'code' => 'Кодът е невалиден или е изтекъл. Изпратете нов код.',
                ])
                ->withInput();
        }

        $user->markEmailAsVerified();
        $request->session()->forget('verification_email');

        return redirect()
            ->route('login')
            ->with('status', 'Имейлът е потвърден успешно. Вече можете да влезете.');
    }

    public function resendVerificationCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'email' => 'Не открихме профил с този имейл.',
                ])
                ->withInput();
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('status', 'Профилът вече е потвърден. Можете да влезете.');
        }

        $this->sendVerificationCode($user);
        $request->session()->put('verification_email', $user->email);

        return back()->with('status', 'Изпратихме нов код за потвърждение.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function sendVerificationCode(User $user): void
    {
        $code = $user->issueEmailVerificationCode();

        Mail::to($user->email)->send(new AccountVerificationCodeMail($user, $code));
    }
}
