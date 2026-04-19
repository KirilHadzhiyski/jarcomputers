@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-xl">
            <div class="card-soft">
                <h1 class="section-heading">Вход</h1>
                <p class="section-copy">Влезте в профила си, за да следите поръчките и историята по тях.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <label class="block text-sm font-medium text-foreground">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" value="{{ old('email') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Парола
                        <input class="input-shell mt-2" type="password" name="password" required>
                    </label>

                    <label class="inline-flex items-center gap-3 text-sm text-muted-foreground">
                        <input type="checkbox" name="remember" value="1">
                        Запомни ме
                    </label>

                    <p class="text-sm text-muted-foreground">
                        <a href="{{ route('password.request') }}" class="font-medium text-primary">Забравена парола?</a>
                    </p>

                    <button type="submit" class="btn-primary">Вход</button>
                </form>

                <div class="mt-6 space-y-2 text-sm text-muted-foreground">
                    <p>
                        Нямате профил?
                        <a href="{{ route('register') }}" class="font-medium text-primary">Регистрация</a>
                    </p>
                    <p>
                        Имате код за потвърждение?
                        <a href="{{ route('verification.notice') }}" class="font-medium text-primary">Потвърдете профила</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
