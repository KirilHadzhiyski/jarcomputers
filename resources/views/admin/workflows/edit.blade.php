@extends('layouts.admin')

@php
    $workflow = $workflow ?? null;
    $isEditing = filled($workflow?->id);
    $categoryValue = old('category_filters', $workflow ? implode("\n", $workflow->category_filters ?? []) : '');
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Админ / Автоматизации',
                    'title' => $isEditing ? "Редакция: {$workflow->name}" : 'Нов работен процес',
                    'description' => 'Опишете източника, графика и контрола при публикуване. След това подредете стъпките на процеса.',
                ])
                @if ($isEditing)
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.workflows.run', $workflow) }}">@csrf<button class="btn-primary" @disabled($workflow->status !== 'active')>Стартирай сега</button></form>
                        <a href="{{ route('admin.workflow-runs.index', ['workflow_id' => $workflow->id]) }}" class="btn-secondary-dark">История на изпълненията</a>
                    </div>
                @endif
            </div>

            <div class="mt-8">@include('partials.admin-nav')</div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEditing ? route('admin.workflows.update', $workflow) : route('admin.workflows.store') }}">
                    @csrf
                    @if ($isEditing) @method('PUT') @endif

                    <div class="admin-form-grid">
                        <div class="admin-field"><label for="name" class="text-sm font-medium">Име</label><input id="name" name="name" value="{{ old('name', $workflow?->name) }}" class="input-shell" required></div>
                        <div class="admin-field"><label for="slug" class="text-sm font-medium">Системен идентификатор</label><input id="slug" name="slug" value="{{ old('slug', $workflow?->slug) }}" class="input-shell" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="генерира се автоматично"></div>
                        <div class="admin-field"><label for="client_user_id" class="text-sm font-medium">Клиентски профил</label><select id="client_user_id" name="client_user_id" class="input-shell"><option value="">Вътрешен процес за JAR Computers</option>@foreach ($clients as $client)<option value="{{ $client->id }}" @selected((string) old('client_user_id', $workflow?->client_user_id) === (string) $client->id)>{{ $client->name }} — {{ $client->currentSubscription?->plan?->name ?? 'без план' }}</option>@endforeach</select></div>
                        <div class="admin-field"><label for="status" class="text-sm font-medium">Статус</label><select id="status" name="status" class="input-shell" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $workflow?->status ?? 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="admin-field"><label for="trigger_type" class="text-sm font-medium">Стартиране</label><select id="trigger_type" name="trigger_type" class="input-shell" required>@foreach ($triggerLabels as $value => $label)<option value="{{ $value }}" @selected(old('trigger_type', $workflow?->trigger_type ?? 'manual') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="admin-field"><label for="frequency_minutes" class="text-sm font-medium">Честота в минути</label><input id="frequency_minutes" name="frequency_minutes" type="number" min="15" max="43200" value="{{ old('frequency_minutes', $workflow?->frequency_minutes) }}" class="input-shell" placeholder="напр. 60"></div>
                        @if ($isEditing && $workflow->trigger_type === 'webhook')
                            <div class="admin-field admin-field-span rounded-2xl border border-border/70 bg-background p-4">
                                <p class="text-sm font-medium">Webhook адрес</p>
                                <code class="mt-2 block break-all text-xs">{{ route('workflows.webhook', $workflow) }}</code>
                                <p class="mt-3 text-xs leading-5 text-muted-foreground">Изпращайте Unix време в <code>X-JAR-Timestamp</code> и <code>X-JAR-Signature: sha256=HMAC_SHA256(timestamp + "." + raw_body, secret)</code>. Приемат се заявки до 5 минути. Тайната се показва само при създаване или ротация.</p>
                                @if (session('webhook_secret_once'))<div class="mt-3 rounded-xl bg-amber-500/10 p-3"><p class="text-xs font-semibold text-amber-800">Копирайте сега — няма да бъде показана отново:</p><code class="mt-2 block break-all text-xs">{{ session('webhook_secret_once') }}</code></div>@endif
                                <label class="mt-4 flex items-center gap-3 text-sm"><input type="checkbox" name="rotate_webhook_secret" value="1"> Генерирай нова тайна при запазване</label>
                            </div>
                        @endif
                        <div class="admin-field"><label for="source_type" class="text-sm font-medium">Тип източник</label><select id="source_type" name="source_type" class="input-shell" required>@foreach ($sourceLabels as $value => $label)<option value="{{ $value }}" @selected(old('source_type', $workflow?->source_type ?? 'rest_api') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="admin-field"><label for="approval_mode" class="text-sm font-medium">Контрол на изхода</label><select id="approval_mode" name="approval_mode" class="input-shell" required>@foreach ($approvalLabels as $value => $label)<option value="{{ $value }}" @selected(old('approval_mode', $workflow?->approval_mode ?? 'review') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="admin-field admin-field-span"><label for="source_url" class="text-sm font-medium">HTTPS адрес на източника</label><input id="source_url" name="source_url" type="url" value="{{ old('source_url', $workflow?->source_url) }}" class="input-shell" placeholder="https://..."></div>
                        <div class="admin-field"><label for="source_auth_type" class="text-sm font-medium">Достъп до източника</label><select id="source_auth_type" name="source_auth_type" class="input-shell" required>@foreach ($sourceAuthLabels as $value => $label)<option value="{{ $value }}" @selected(old('source_auth_type', $workflow?->source_auth_type ?? 'none') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="admin-field"><label for="source_token" class="text-sm font-medium">Bearer token</label><input id="source_token" name="source_token" type="password" autocomplete="new-password" class="input-shell" placeholder="{{ filled($workflow?->source_credentials['token'] ?? null) ? 'Запазен — оставете празно без промяна' : 'Само ако е необходим' }}"></div>
                        <div class="admin-field"><label for="source_username" class="text-sm font-medium">API потребител</label><input id="source_username" name="source_username" autocomplete="off" class="input-shell" value="{{ old('source_username', $workflow?->source_credentials['username'] ?? '') }}"></div>
                        <div class="admin-field"><label for="source_password" class="text-sm font-medium">API парола</label><input id="source_password" name="source_password" type="password" autocomplete="new-password" class="input-shell" placeholder="{{ filled($workflow?->source_credentials['password'] ?? null) ? 'Запазена — оставете празно без промяна' : 'Само ако е необходима' }}"></div>
                        <div class="admin-field admin-field-span"><p class="rounded-xl bg-primary/5 p-3 text-xs leading-5 text-muted-foreground">Разрешени домейни: {{ $allowedSourceHosts ? implode(', ', $allowedSourceHosts) : 'няма — задайте WORKFLOW_SOURCE_HOSTS в .env преди изпълнение' }}. Данните за достъп се пазят криптирано.</p></div>
                        <div class="admin-field admin-field-span"><label for="category_filters" class="text-sm font-medium">Филтри по категории</label><textarea id="category_filters" name="category_filters" rows="4" class="input-shell h-auto" placeholder="Една категория на ред">{{ $categoryValue }}</textarea></div>
                        <div class="admin-field admin-field-span"><label for="description" class="text-sm font-medium">Цел и правила</label><textarea id="description" name="description" rows="5" class="input-shell h-auto">{{ old('description', $workflow?->description) }}</textarea></div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-border/70 pt-6">
                        <button class="btn-primary">{{ $isEditing ? 'Запази настройките' : 'Създай процес' }}</button>
                        <a href="{{ route('admin.workflows.index') }}" class="btn-secondary-dark">Отказ</a>
                    </div>
                </form>

                @if ($isEditing)
                    <form method="POST" action="{{ route('admin.workflows.destroy', $workflow) }}" class="mt-4" onsubmit="return confirm('Да изтрия ли процеса и цялата му история?')">
                        @csrf @method('DELETE')
                        <button class="text-sm font-medium text-rose-600">Изтрий процеса</button>
                    </form>
                @endif
            </div>

            @if ($isEditing)
                <div class="mt-8 grid gap-6 xl:grid-cols-[1.5fr,0.8fr]">
                    <div class="card-soft">
                        <div class="flex items-start justify-between gap-4"><div><h2 class="text-xl font-semibold">Визуален поток</h2><p class="mt-2 text-sm text-muted-foreground">Стъпките се изпълняват отляво надясно. Използвайте стрелките за промяна на реда.</p></div><span class="status-pill status-pill-{{ $workflow->status }}">{{ $statusLabels[$workflow->status] }}</span></div>

                        @if ($workflow->steps->isEmpty())
                            <div class="admin-empty-state mt-6">Добавете първата стъпка — например „Събиране от REST API“.</div>
                        @else
                            <div class="mt-6 flex gap-4 overflow-x-auto pb-4">
                                @foreach ($workflow->steps as $index => $step)
                                    <article class="min-w-[240px] rounded-2xl border border-border/70 bg-background p-4 shadow-sm {{ $step->is_enabled ? '' : 'opacity-55' }}">
                                        <div class="flex items-center justify-between gap-3"><span class="inline-flex size-8 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">{{ $step->position }}</span><span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $stepTypeLabels[$step->type] ?? $step->type }}</span></div>
                                        <h3 class="mt-4 font-semibold">{{ $step->name }}</h3>
                                        @if ($step->configuration)<pre class="mt-3 max-h-28 overflow-auto rounded-lg bg-slate-950 p-3 text-[11px] leading-5 text-slate-100">{{ json_encode($step->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>@endif
                                        <div class="mt-4 flex items-center gap-2">
                                            <form method="POST" action="{{ route('admin.workflows.steps.move', [$workflow, $step]) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="up"><button class="btn-secondary-dark px-3 py-1.5" @disabled($loop->first)>←</button></form>
                                            <form method="POST" action="{{ route('admin.workflows.steps.move', [$workflow, $step]) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="down"><button class="btn-secondary-dark px-3 py-1.5" @disabled($loop->last)>→</button></form>
                                            <form method="POST" action="{{ route('admin.workflows.steps.destroy', [$workflow, $step]) }}" class="ml-auto" onsubmit="return confirm('Да премахна ли тази стъпка?')">@csrf @method('DELETE')<button class="text-sm font-medium text-rose-600">Премахни</button></form>
                                        </div>
                                    </article>
                                    @if (! $loop->last)<div class="flex min-w-5 items-center justify-center text-xl text-primary">→</div>@endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="card-soft">
                        <h2 class="text-xl font-semibold">Добавяне на стъпка</h2>
                        <p class="mt-2 text-sm text-muted-foreground">За стъпка „Събиране“ може да зададете items_path, field_map, GraphQL query или CSV delimiter. Тайни не се поставят в JSON.</p>
                        <form method="POST" action="{{ route('admin.workflows.steps.store', $workflow) }}" class="mt-6 flex flex-col gap-5">
                            @csrf
                            <div class="admin-field"><label for="step_name" class="text-sm font-medium">Име</label><input id="step_name" name="name" class="input-shell" required></div>
                            <div class="admin-field"><label for="step_type" class="text-sm font-medium">Тип</label><select id="step_type" name="type" class="input-shell" required>@foreach ($stepTypeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                            <div class="admin-field"><label for="configuration" class="text-sm font-medium">Конфигурация (JSON)</label><textarea id="configuration" name="configuration" rows="7" class="input-shell h-auto font-mono text-xs" placeholder='{"source":"skroutz"}'></textarea></div>
                            <label class="flex items-center gap-3 text-sm font-medium"><input type="checkbox" name="is_enabled" value="1" class="size-4 rounded border-input" checked> Активна стъпка</label>
                            <button class="btn-primary">Добави стъпка</button>
                        </form>
                    </div>
                </div>

                <div class="card-soft mt-8">
                    <h2 class="text-xl font-semibold">Безопасност при публикуване</h2>
                    <p class="mt-3 max-w-4xl text-sm leading-6 text-muted-foreground">Процесът вече може да събира и нормализира данни от разрешени REST, GraphQL, RSS, JSON и CSV източници. Публикуването към marketplace остава блокирано без отделен активен конектор, live режим и проследимо одобрение.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
