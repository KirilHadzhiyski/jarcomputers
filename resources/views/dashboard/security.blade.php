@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-4xl">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Профил</p>
                    <h1 class="section-heading">Сигурност</h1>
                    <p class="section-copy">Защитете входа с еднократни кодове от Google Authenticator, 2FAS, Microsoft Authenticator или друго TOTP приложение.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn-secondary-dark">Към профила</a>
            </div>

            <div class="card-soft mt-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-foreground">Двуфакторна защита</h2>
                        <p class="mt-2 text-sm text-muted-foreground">След паролата системата ще изисква и временен код.</p>
                    </div>
                    <span class="status-pill {{ auth()->user()->hasTwoFactorEnabled() ? 'status-pill-active' : 'status-pill-paused' }}">
                        {{ auth()->user()->hasTwoFactorEnabled() ? 'Активна' : 'Неактивна' }}
                    </span>
                </div>

                @if ($recoveryCodes)
                    <div class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950">
                        <h3 class="font-semibold">Запазете тези резервни кодове сега</h3>
                        <p class="mt-2 text-sm">Всеки код работи само веднъж и няма да бъде показан повторно.</p>
                        <div class="mt-4 grid gap-2 font-mono text-sm sm:grid-cols-2">
                            @foreach ($recoveryCodes as $recoveryCode)
                                <code class="rounded-lg bg-white px-3 py-2">{{ $recoveryCode }}</code>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($formattedSecret && ! auth()->user()->hasTwoFactorEnabled())
                    <div class="mt-6 rounded-2xl border bg-background p-5">
                        <h3 class="font-semibold text-foreground">1. Добавете профила в приложението</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Изберете ръчно добавяне на ключ, тип „Time based / TOTP“, име JAR Computers.</p>
                        <div class="mt-4 break-all rounded-xl border bg-white p-4 font-mono text-base font-semibold tracking-wider">{{ $formattedSecret }}</div>
                        <a href="{{ $otpauthUri }}" class="mt-4 inline-flex text-sm font-semibold text-primary">Отвори директно в съвместимо приложение</a>

                        <form method="POST" action="{{ route('security.two-factor.confirm') }}" class="mt-6 flex flex-col gap-4">
                            @csrf
                            <label class="block text-sm font-medium text-foreground">
                                2. Въведете показания 6-цифрен код
                                <input class="input-shell mt-2 font-mono tracking-widest" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required>
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="btn-primary">Активирай защитата</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('security.two-factor.cancel') }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-muted-foreground">Откажи настройката</button>
                        </form>
                    </div>
                @elseif (auth()->user()->hasTwoFactorEnabled())
                    <div class="mt-6 grid gap-6 lg:grid-cols-2">
                        <form method="POST" action="{{ route('security.two-factor.recovery-codes') }}" class="rounded-2xl border bg-background p-5">
                            @csrf
                            <h3 class="font-semibold text-foreground">Нови резервни кодове</h3>
                            <p class="mt-2 text-sm text-muted-foreground">Старите кодове ще бъдат обезсилени.</p>
                            <label class="mt-4 block text-sm font-medium">Текуща парола<input class="input-shell mt-2" type="password" name="password" autocomplete="current-password" required></label>
                            <label class="mt-4 block text-sm font-medium">TOTP или резервен код<input class="input-shell mt-2" type="text" name="code" autocomplete="one-time-code" required></label>
                            <button type="submit" class="btn-secondary-dark mt-5">Генерирай нови кодове</button>
                        </form>

                        <form method="POST" action="{{ route('security.two-factor.destroy') }}" class="rounded-2xl border border-destructive/30 bg-destructive/5 p-5">
                            @csrf
                            @method('DELETE')
                            <h3 class="font-semibold text-foreground">Изключване</h3>
                            <p class="mt-2 text-sm text-muted-foreground">Това премахва втория фактор и резервните кодове.</p>
                            <label class="mt-4 block text-sm font-medium">Текуща парола<input class="input-shell mt-2" type="password" name="password" autocomplete="current-password" required></label>
                            <label class="mt-4 block text-sm font-medium">TOTP или резервен код<input class="input-shell mt-2" type="text" name="code" autocomplete="one-time-code" required></label>
                            <button type="submit" class="mt-5 rounded-xl bg-destructive px-5 py-3 text-sm font-semibold text-white">Изключи защитата</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('security.two-factor.prepare') }}" class="mt-6 max-w-lg">
                        @csrf
                        <label class="block text-sm font-medium text-foreground">
                            Потвърдете текущата парола
                            <input class="input-shell mt-2" type="password" name="password" autocomplete="current-password" required>
                        </label>
                        <button type="submit" class="btn-primary mt-5">Започни настройката</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
