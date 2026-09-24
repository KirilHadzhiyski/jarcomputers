@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">{{ $configuration->name }}</h1><p class="section-copy">Данни за публикуване в Skroutz. Може да запазите непълна чернова.</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <form method="POST" action="{{ route('admin.skroutz.update', $configuration) }}" class="card-soft space-y-5">@csrf @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <label>Име на продукта<input class="input-shell mt-2" name="name" value="{{ old('name', $configuration->name) }}" required maxlength="255"></label>
            <label>Постоянен SKU / XML ID<input class="input-shell mt-2" name="sku" value="{{ old('sku', $configuration->sku) }}" required maxlength="100"></label>
            <label class="md:col-span-2">Компоненти: CPU, GPU, RAM, SSD, дънна платка, захранване, кутия<textarea class="input-shell mt-2" name="component_summary" rows="3">{{ old('component_summary', $configuration->component_summary) }}</textarea></label>
            @foreach (\App\Services\Integrations\SkroutzCatalog::FIELDS as $field => $label)
                <label class="{{ $field === 'description' ? 'md:col-span-2' : '' }}">{{ $label }}
                @if ($field === 'description')<textarea class="input-shell mt-2" name="product[{{ $field }}]" rows="4">{{ old('product.'.$field, $data[$field] ?? '') }}</textarea>
                @elseif ($field === 'availability')<select class="input-shell mt-2" name="product[{{ $field }}]"><option value="">Изберете потвърден срок</option>@foreach (\App\Services\Integrations\SkroutzCatalog::AVAILABILITY as $value)<option value="{{ $value }}" @selected(old('product.'.$field, $data[$field] ?? '') === $value)>{{ $value }}</option>@endforeach</select>
                @else<input class="input-shell mt-2" name="product[{{ $field }}]" type="{{ in_array($field, ['link', 'image']) ? 'url' : (in_array($field, ['price_with_vat', 'vat', 'quantity', 'weight']) ? 'number' : 'text') }}" @if(in_array($field, ['price_with_vat', 'vat', 'weight'])) step="0.01" min="0" @elseif($field === 'quantity') step="1" min="0" @endif value="{{ old('product.'.$field, $data[$field] ?? '') }}">@endif</label>
            @endforeach
        </div>
        <label class="flex gap-3 items-center"><input type="checkbox" name="approved" value="1" @checked(old('approved', $configuration->skroutzListing?->approved))> Проверих данните и одобрявам включване в каталога</label>
        <p class="text-sm text-muted-foreground">Количеството е реалният запас за продажба. Доставчикова наличност от JAR не се превръща автоматично в собствен склад. За собствена PC сглобка потвърдете с Skroutz правилата за производител, MPN и EAN.</p>
        <div class="flex gap-4"><button class="btn-primary">Запази конфигурацията</button><a class="btn-secondary-dark" href="{{ route('admin.skroutz.index') }}">Назад</a></div>
    </form>
</div></section>
@endsection
