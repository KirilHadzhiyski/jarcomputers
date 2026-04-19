@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-xl">
            <div class="card-soft">
                <h1 class="section-heading">Регистрация</h1>
                <p class="section-copy">След регистрация ще получите 6-цифрен код по имейл. Профилът се активира след потвърждение на кода.</p>

                <form method="POST" action="{{ route('register.store') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <label class="block text-sm font-medium text-foreground">
                        Име
                        <input class="input-shell mt-2" type="text" name="name" value="{{ old('name') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" value="{{ old('email') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Телефон
                        <input class="input-shell mt-2" type="text" name="phone" value="{{ old('phone') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Предпочитан контакт
                        <select class="input-shell mt-2" name="preferred_contact_channel">
                            @foreach ($contactChannels as $value => $label)
                                <option value="{{ $value }}" @selected(old('preferred_contact_channel', 'email') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Парола
                        <input class="input-shell mt-2" type="password" name="password" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Потвърди паролата
                        <input class="input-shell mt-2" type="password" name="password_confirmation" required>
                    </label>

                    <button type="submit" class="btn-primary">Създай профил</button>
                </form>

                <p class="mt-6 text-sm text-muted-foreground">
                    Вече имате профил?
                    <a href="{{ route('login') }}" class="font-medium text-primary">Вход</a>
                </p>
            </div>
        </div>
    </section>
@endsection
