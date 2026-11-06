@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-xl">
            <div class="card-soft">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Защитен вход</p>
                <h1 class="section-heading mt-2">Двуфакторна проверка</h1>
                <p class="section-copy">Въведете 6-цифрения код от приложението за удостоверяване за {{ $maskedEmail }}. Можете да използвате и един от резервните кодове.</p>

                <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <label class="block text-sm font-medium text-foreground">
                        Код или резервен код
                        <input class="input-shell mt-2 font-mono tracking-widest" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required>
                    </label>

                    <button type="submit" class="btn-primary">Потвърди входа</button>
                </form>

                <p class="mt-6 text-sm text-muted-foreground">
                    <a href="{{ route('login') }}" class="font-medium text-primary">Връщане към входа</a>
                </p>
            </div>
        </div>
    </section>
@endsection
