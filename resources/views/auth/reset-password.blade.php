@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-xl">
            <div class="card-soft">
                <h1 class="section-heading">Смяна на парола</h1>
                <p class="section-copy">Изберете нова парола за профила си. Линкът е временен и работи само за заявения имейл.</p>

                <form method="POST" action="{{ route('password.update') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <label class="block text-sm font-medium text-foreground">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" value="{{ $email }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Нова парола
                        <input class="input-shell mt-2" type="password" name="password" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Потвърди паролата
                        <input class="input-shell mt-2" type="password" name="password_confirmation" required>
                    </label>

                    <button type="submit" class="btn-primary">Запази новата парола</button>
                </form>

                <p class="mt-6 text-sm text-muted-foreground">
                    Назад към
                    <a href="{{ route('login') }}" class="font-medium text-primary">вход</a>.
                </p>
            </div>
        </div>
    </section>
@endsection
