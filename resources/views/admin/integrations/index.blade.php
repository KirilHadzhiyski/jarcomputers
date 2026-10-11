@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">Интеграции и автоматизация</h1>
    <p class="section-copy">Свържете източниците веднъж. Данните се обновяват по график, с проследим резултат от всеки импорт.</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <div class="card-soft mb-8">
        <h2 class="text-xl font-semibold">Автоматично получаване</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-3 text-sm">
            <div><strong>Заявки от сайта</strong><p class="mt-2 text-muted-foreground">Веднага → клиент, бизнес поръчка и разговор.</p></div>
            <div><strong>Проверка на графика</strong><p class="mt-2 text-muted-foreground">{{ $schedulerHeartbeat ? \Illuminate\Support\Carbon::parse($schedulerHeartbeat)->diffForHumans() : 'Няма отчетен старт на планировчика' }}</p></div>
            <div><strong>Последна обработка на API задача</strong><p class="mt-2 text-muted-foreground">{{ $workerHeartbeat ? \Illuminate\Support\Carbon::parse($workerHeartbeat)->diffForHumans() : 'Все още няма обработена задача' }}</p></div>
        </div>
        @if (! $schedulerHeartbeat || \Illuminate\Support\Carbon::parse($schedulerHeartbeat)->lt(now()->subMinutes(3)))
            <p class="mt-4 rounded-xl bg-amber-500/10 p-4 text-sm">Графикът не е активен. За локална работа стартирайте <code>run-automation.cmd</code>. При хостинг се настройват scheduler и queue worker по ръководството в проекта.</p>
        @endif
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('admin.inbox.index') }}" class="btn-secondary-dark">Входящи заявки</a>
            <a href="{{ route('admin.integrations.catalog') }}" class="btn-secondary-dark">Получен каталог</a>
            <a href="{{ route('admin.integrations.documents') }}" class="btn-secondary-dark">Получени документи</a>
            <a href="{{ route('admin.integrations.index') }}" class="btn-secondary-dark">Обнови статуса</a>
        </div>
    </div>
    <div class="grid gap-6 lg:grid-cols-2">
    @foreach ($definitions as $provider => $definition)
        @php
            $connection = $connections->get($provider);
            $missing = $connection ? \App\Services\Integrations\IntegrationRegistry::missing($connection) : ['Необходими са миграции'];
            $latest = $connection?->runs->first();
        @endphp
        <article class="card-soft" id="integration-{{ $provider }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">{{ $definition['name'] }}</h2>
                <span class="rounded-full px-3 py-1 text-xs {{ $missing ? 'bg-amber-500/10 text-amber-700' : ($connection?->enabled ? 'bg-emerald-500/10 text-emerald-700' : 'bg-secondary text-muted-foreground') }}">{{ $missing ? 'Нужни настройки' : ($connection?->enabled ? 'По график' : 'Ръчно обновяване') }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-muted-foreground">{{ $definition['description'] }}</p>
            <p class="mt-3 text-sm">На {{ $definition['interval'] }} мин. · Последно успешно: {{ $connection?->last_success_at?->format('d.m.Y H:i') ?? 'няма' }}</p>
            @if ($latest?->status === 'failed')<p class="mt-3 text-sm text-destructive">{{ $latest->message }}</p>@endif
            <details class="mt-5" @if ($missing) open @endif>
                <summary class="cursor-pointer font-medium text-primary">Настройки на връзката</summary>
                <form action="{{ route('admin.integrations.update', $provider) }}" method="POST" class="mt-4 space-y-4" autocomplete="off">
                    @csrf @method('PUT')
                    @foreach ($definition['credentials'] as $key => $label)
                        <div class="admin-field"><label for="{{ $provider }}-{{ $key }}" class="text-sm font-medium">{{ $label }}</label>
                        <input id="{{ $provider }}-{{ $key }}" type="password" name="credentials[{{ $key }}]" class="input-shell" maxlength="4096" autocomplete="new-password" placeholder="{{ filled($connection?->credentials[$key] ?? null) ? 'Запазено — празно поле запазва стойността' : 'Не е въведено' }}"></div>
                    @endforeach
                    @foreach ($definition['settings'] as $key => $label)
                        <div class="admin-field"><label for="{{ $provider }}-setting-{{ $key }}" class="text-sm font-medium">{{ $label }}</label>
                        @if ($key === 'service')
                            <select id="{{ $provider }}-setting-{{ $key }}" name="settings[{{ $key }}]" class="input-shell"><option value="">Изберете услуга</option>@foreach (['microsoft'=>'Microsoft 365 / Outlook','gmail'=>'Google Workspace / Gmail'] as $value=>$label)<option value="{{ $value }}" @selected(($connection?->settings[$key] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                        @elseif ($key === 'currency')
                            <select id="{{ $provider }}-setting-{{ $key }}" name="settings[{{ $key }}]" class="input-shell"><option value="">Изберете потвърдена валута</option>
                            @foreach (['EUR','BGN','USD','GBP','RON','PLN','CZK','HUF'] as $currency)<option value="{{ $currency }}" @selected(($connection?->settings[$key] ?? '') === $currency)>{{ $currency }}</option>@endforeach</select>
                        @elseif ($key === 'products_mode')
                            <select id="{{ $provider }}-setting-{{ $key }}" name="settings[{{ $key }}]" class="input-shell">
                                @foreach (['off'=>'Изключено — само поръчки','validation'=>'Валидация — без промени в Skroutz','live'=>'Публикуване — цени и наличности'] as $value=>$optionLabel)<option value="{{ $value }}" @selected(($connection?->settings[$key] ?? 'off') === $value)>{{ $optionLabel }}</option>@endforeach
                            </select>
                        @else
                            <input id="{{ $provider }}-setting-{{ $key }}" name="settings[{{ $key }}]" type="{{ $key === 'url' ? 'url' : 'text' }}" value="{{ $connection?->settings[$key] ?? '' }}" class="input-shell">
                        @endif</div>
                    @endforeach
                    <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="enabled" value="1" @checked($connection?->enabled)> Автоматично получаване по график</label>
                    @if ($definition['credentials'])<label class="flex items-center gap-3 text-sm text-muted-foreground"><input type="checkbox" name="clear_credentials" value="1"> Изчисти запазения достъп</label>@endif
                    <p class="text-xs leading-6 text-muted-foreground">{{ $definition['help'] }}</p>
                    <button class="btn-primary" type="submit">Запази {{ $definition['name'] }}</button>
                </form>
            </details>
            <div class="mt-5 flex flex-wrap items-center gap-4">
                <form action="{{ route('admin.integrations.sync', $provider) }}" method="POST">@csrf
                    <button class="btn-secondary-dark disabled:opacity-50" @disabled($missing || in_array($latest?->status, ['queued', 'running']))>{{ in_array($latest?->status, ['queued', 'running']) ? 'Чака / обработва се' : 'Обнови сега' }}</button>
                </form>
                @if ($definition['docs'])<a class="text-sm text-primary" href="{{ $definition['docs'] }}" target="_blank" rel="noopener noreferrer">Официална документация ↗</a>@endif
            </div>
        </article>
    @endforeach
    </div>
    <div class="card-soft mt-8">
        <h2 class="text-xl font-semibold">Канали за клиентски съобщения</h2>
        <p class="mt-3 text-sm text-muted-foreground">Получените съобщения от активирани канали се събират във входящата кутия. Достъпът до каналите се задава на сървъра.</p>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
        @foreach (['whatsapp', 'facebook-messenger', 'viber'] as $channel)
            @php($channelConfig = config('communications.channels.'.$channel))
            <div class="rounded-xl border border-border p-4"><strong>{{ $channelConfig['label'] }}</strong><p class="mt-2 text-sm">{{ $channelConfig['enabled'] ? 'Включен — проверете webhook достъпа' : 'Изключен — нужен бизнес акаунт / bot' }}</p><p class="mt-2 break-all text-xs text-muted-foreground">{{ url('/webhooks/'.$channel) }}</p></div>
        @endforeach
        </div>
    </div>
    <div class="card-soft mt-8">
        <h2 class="text-xl font-semibold">История на обновяванията</h2>
        <div class="admin-table-wrap mt-5"><table class="min-w-full text-left text-sm"><thead><tr><th class="p-3">Източник</th><th class="p-3">Резултат</th><th class="p-3">Записи</th><th class="p-3">Начало</th><th class="p-3">Подробности</th></tr></thead><tbody>
        @forelse ($runs as $run)
            <tr class="border-t border-border"><td class="p-3">{{ $definitions[$run->connection->provider]['name'] ?? $run->connection->provider }}</td><td class="p-3">{{ ['queued'=>'В опашка','running'=>'Обработва се','succeeded'=>'Успешно','failed'=>'Неуспешно','cancelled'=>'Пропуснато'][$run->status] ?? $run->status }}</td><td class="p-3">{{ $run->record_count }}</td><td class="p-3 whitespace-nowrap">{{ ($run->started_at ?? $run->created_at)->format('d.m H:i') }}</td><td class="p-3">{{ $run->message ?? 'Очаква обработка от worker' }}</td></tr>
        @empty<tr><td colspan="5" class="p-6 text-muted-foreground">Все още няма стартирани обновявания.</td></tr>@endforelse
        </tbody></table></div><div class="mt-4">{{ $runs->links() }}</div>
    </div>
    @if ($rates->isNotEmpty())
    <details class="card-soft mt-8"><summary class="cursor-pointer font-semibold">Получени курсове — {{ $rates->first()->as_of }}</summary><div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">@foreach ($rates as $rate)<p class="text-sm">1 EUR = {{ rtrim(rtrim($rate->units_per_eur, '0'), '.') }} {{ $rate->currency_code }}</p>@endforeach</div><p class="mt-4 text-xs text-muted-foreground">BGN е фиксирана историческа конверсия за съществуващите полета на ценовия модул.</p></details>
    @endif
</div></section>
@endsection
