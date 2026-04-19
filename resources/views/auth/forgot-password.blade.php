@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-xl">
            <div class="card-soft">
                <h1 class="section-heading">Забравена парола</h1>
                <p class="section-copy">Въведете имейла на профила. Ако адресът съществува в системата, ще получите линк за смяна на паролата.</p>

                <form method="POST" action="{{ route('password.email') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <label class="block text-sm font-medium text-foreground">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" value="{{ old('email') }}" required>
                    </label>

                    <button type="submit" class="btn-primary">Изпрати линк</button>
                </form>

                <div class="mt-6 space-y-2 text-sm text-muted-foreground">
                    <p>
                        Спомнихте си паролата?
                        <a href="{{ route('login') }}" class="font-medium text-primary">Вход</a>
                    </p>
                    <p>
                        Нямате профил?
                        <a href="{{ route('register') }}" class="font-medium text-primary">Регистрация</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
