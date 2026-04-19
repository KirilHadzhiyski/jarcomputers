@php
    $site = config('site');
    $formId = $formId ?? 'repair-form';
    $modelOptions = $models ?? \App\Support\SiteData::modelsForSelect();
    if (($modelOptions[0] ?? null) && is_array($modelOptions[0])) {
        $modelOptions = collect($modelOptions)->pluck('name')->push('Друг модел')->values()->all();
    }
    $sourcePage = $sourcePage ?? request()->getPathInfo();
    $initialSuccess = session('repair_request_success', false);
    $props = [
        'endpoint' => route('repair-requests.store'),
        'sourcePage' => $sourcePage,
        'brand' => $site['brand'],
        'models' => $modelOptions,
        'privacyUrl' => route('privacy'),
        'initialSuccess' => $initialSuccess,
    ];
@endphp

<div id="{{ $formId }}">
    <div
        data-vue-component="repair-form"
        data-props='@json($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'
    >
        @if ($initialSuccess)
            <div class="success-panel">
                <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl">
                    ✓
                </div>
                <h3 class="mb-2 text-xl font-bold text-slate-950">Заявката е приета</h3>
                <p class="text-sm leading-7 text-slate-600">
                    Ще се свържем с вас в рамките на 1 час в работно време.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('repair-requests.store') }}" class="card-soft space-y-4">
                @csrf
                <input type="hidden" name="source_page" value="{{ $sourcePage }}">
                <input type="hidden" name="form_fragment" value="{{ $formId }}">

                <div>
                    <h3 class="text-xl font-bold text-slate-950">Заявка за ремонт</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        Формата записва заявката в backend системата и създава комуникационна история за телефона ви.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Проверете попълнените полета и опитайте отново.
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-800">
                        Име *
                        <input class="input-shell mt-2" type="text" name="name" maxlength="100" value="{{ old('name') }}" placeholder="Вашето име">
                        @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block text-sm font-medium text-slate-800">
                        Телефон *
                        <input class="input-shell mt-2" type="tel" name="phone" maxlength="20" value="{{ old('phone') }}" placeholder="0878 369 024">
                        @error('phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-800">
                        Имейл
                        <input class="input-shell mt-2" type="email" name="email" maxlength="120" value="{{ old('email') }}" placeholder="name@example.com">
                        @error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block text-sm font-medium text-slate-800">
                        Град
                        <input class="input-shell mt-2" type="text" name="city" maxlength="50" value="{{ old('city') }}" placeholder="Напр. София">
                        @error('city')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="block text-sm font-medium text-slate-800">
                    Модел iPhone
                    <select class="input-shell mt-2" name="model">
                        <option value="">Изберете модел</option>
                        @foreach ($modelOptions as $model)
                            <option value="{{ $model }}" @selected(old('model') === $model)>{{ $model }}</option>
                        @endforeach
                    </select>
                    @error('model')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium text-slate-800">
                    Описание на проблема *
                    <textarea class="input-shell mt-2 min-h-32 resize-none" name="issue" rows="4" maxlength="1000" placeholder="Опишете проблема, кога се проявява и дали има следи от удар, вода или предишен ремонт.">{{ old('issue') }}</textarea>
                    @error('issue')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <fieldset>
                    <legend class="text-sm font-medium text-slate-800">Предпочитан контакт</legend>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach (['phone' => 'Телефон', 'viber' => 'Viber', 'whatsapp' => 'WhatsApp', 'email' => 'Имейл'] as $value => $label)
                            <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700">
                                <input type="radio" name="preferred_contact" value="{{ $value }}" @checked(old('preferred_contact', 'phone') === $value)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('preferred_contact')<span class="mt-2 block text-xs text-red-600">{{ $message }}</span>@enderror
                </fieldset>

                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm leading-6 text-slate-700">
                    <input type="checkbox" name="gdpr_consent" value="1" class="mt-1" @checked(old('gdpr_consent'))>
                    <span>
                        Съгласен/на съм данните ми да бъдат обработени за целите на заявката и комуникацията по ремонта според
                        <a href="{{ route('privacy') }}" class="font-semibold text-blue-700 underline underline-offset-4">политиката за поверителност</a>.
                    </span>
                </label>
                @error('gdpr_consent')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror

                <button type="submit" class="btn-primary w-full">
                    Изпрати заявка за ремонт
                </button>

                <p class="text-center text-xs leading-6 text-slate-500">
                    Безплатна диагностика • Проследяване на заявката • {{ $site['brand'] }}
                </p>
            </form>
        @endif
    </div>
</div>
