@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container max-w-3xl">
            <div class="card-soft">
                <h1 class="section-heading">Нова поръчка</h1>
                <p class="section-copy">Подайте поръчка за ремонт или сервизен въпрос. След създаване ще можете да проследявате статуса в профила си.</p>

                <form method="POST" action="{{ route('tickets.store') }}" class="mt-8 flex flex-col gap-5">
                    @csrf

                    <label class="block text-sm font-medium text-foreground">
                        Тема
                        <input class="input-shell mt-2" type="text" name="subject" value="{{ old('subject') }}" required>
                    </label>

                    <label class="block text-sm font-medium text-foreground">
                        Модел устройство
                        <input class="input-shell mt-2" type="text" name="device_model" value="{{ old('device_model') }}">
                    </label>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block text-sm font-medium text-foreground">
                            Категория
                            <select class="input-shell mt-2" name="category">
                                @foreach ($categoryLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', 'repair') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-foreground">
                            Приоритет
                            <select class="input-shell mt-2" name="priority">
                                @foreach ($priorityLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label class="block text-sm font-medium text-foreground">
                        Описание
                        <textarea class="input-shell mt-2 min-h-40 resize-none" name="description" rows="6" required>{{ old('description') }}</textarea>
                    </label>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="btn-primary">Създай поръчка</button>
                        <a href="{{ route('dashboard') }}" class="btn-secondary-dark">Назад</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
