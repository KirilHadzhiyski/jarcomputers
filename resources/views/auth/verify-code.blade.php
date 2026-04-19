@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-xl">
            <div class="card-soft">
                <h1 class="section-heading">Потвърждение на профил</h1>
                <p class="section-copy">Въведете 6-цифрения код, изпратен на имейла ви. Без това потвърждение профилът не може да се използва за вход.</p>

                <form method="POST" action="{{ route('verification.verify') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <label class="block text-sm font-medium text-foreground">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" value="{{ $verificationEmail }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Код
                        <input class="input-shell mt-2" type="text" name="code" inputmode="numeric" maxlength="6" value="{{ old('code') }}" required>
                    </label>

                    <button type="submit" class="btn-primary">Потвърди профила</button>
                </form>

                <form method="POST" action="{{ route('verification.resend') }}" class="mt-4 flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="email" value="{{ $verificationEmail }}">
                    <button type="submit" class="btn-secondary-dark">Изпрати нов код</button>
                </form>

                <p class="mt-6 text-sm text-muted-foreground">
                    След успешно потвърждение ще можете да влезете от
                    <a href="{{ route('login') }}" class="font-medium text-primary">страницата за вход</a>.
                </p>
            </div>
        </div>
    </section>
@endsection
