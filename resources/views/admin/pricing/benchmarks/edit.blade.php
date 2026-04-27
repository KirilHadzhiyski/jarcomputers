@extends('layouts.site')

@php
    $benchmark = $benchmark ?? null;
    $isEditing = filled($benchmark?->id);
    $collectedAt = old('collected_at', $benchmark?->collected_at?->format('Y-m-d\TH:i'));
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Pricing',
                'title' => $isEditing ? 'Edit benchmark' : 'New benchmark',
                'description' => 'Capture competitor prices as either manual observations or scraper-provided records.',
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEditing ? route('admin.pricing.benchmarks.update', $benchmark) : route('admin.pricing.benchmarks.store') }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="pricing_configuration_id" class="text-sm font-medium text-foreground">Configuration</label>
                            <select id="pricing_configuration_id" name="pricing_configuration_id" class="input-shell" required>
                                @foreach ($configurations as $configuration)
                                    <option value="{{ $configuration->id }}" @selected((string) old('pricing_configuration_id', $benchmark?->pricing_configuration_id) === (string) $configuration->id)>{{ $configuration->name }} ({{ $configuration->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="pricing_market_id" class="text-sm font-medium text-foreground">Market</label>
                            <select id="pricing_market_id" name="pricing_market_id" class="input-shell" required>
                                @foreach ($markets as $market)
                                    <option value="{{ $market->id }}" @selected((string) old('pricing_market_id', $benchmark?->pricing_market_id) === (string) $market->id)>{{ $market->name }} ({{ $market->currency_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="pricing_source_id" class="text-sm font-medium text-foreground">Source</label>
                            <select id="pricing_source_id" name="pricing_source_id" class="input-shell" required>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->id }}" @selected((string) old('pricing_source_id', $benchmark?->pricing_source_id) === (string) $source->id)>{{ $source->name }} / {{ $source->market?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="observed_price" class="text-sm font-medium text-foreground">Observed price</label>
                            <input id="observed_price" name="observed_price" type="number" step="0.01" min="0.01" value="{{ old('observed_price', $benchmark?->observed_price) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="currency_code" class="text-sm font-medium text-foreground">Currency</label>
                            <input id="currency_code" name="currency_code" type="text" value="{{ old('currency_code', $benchmark?->currency_code ?? 'EUR') }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="input_method" class="text-sm font-medium text-foreground">Input method</label>
                            <select id="input_method" name="input_method" class="input-shell" required>
                                @foreach ($inputMethodLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('input_method', $benchmark?->input_method ?? 'manual') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="availability_text" class="text-sm font-medium text-foreground">Availability</label>
                            <input id="availability_text" name="availability_text" type="text" value="{{ old('availability_text', $benchmark?->availability_text) }}" class="input-shell">
                        </div>
                        <div class="admin-field">
                            <label for="competitor_name" class="text-sm font-medium text-foreground">Competitor</label>
                            <input id="competitor_name" name="competitor_name" type="text" value="{{ old('competitor_name', $benchmark?->competitor_name) }}" class="input-shell">
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="product_title" class="text-sm font-medium text-foreground">Product title</label>
                            <input id="product_title" name="product_title" type="text" value="{{ old('product_title', $benchmark?->product_title) }}" class="input-shell">
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="product_url" class="text-sm font-medium text-foreground">Product URL</label>
                            <input id="product_url" name="product_url" type="url" value="{{ old('product_url', $benchmark?->product_url) }}" class="input-shell">
                        </div>
                        <div class="admin-field">
                            <label for="collected_at" class="text-sm font-medium text-foreground">Collected at</label>
                            <input id="collected_at" name="collected_at" type="datetime-local" value="{{ $collectedAt }}" class="input-shell">
                        </div>
                        <div class="admin-field admin-field-span">
                            <label class="flex items-center gap-3 text-sm font-medium text-foreground">
                                <input type="checkbox" name="price_includes_vat" value="1" class="size-4 rounded border-input" @checked(old('price_includes_vat', $benchmark?->price_includes_vat ?? true))>
                                Observed price includes VAT
                            </label>
                            <label class="mt-3 flex items-center gap-3 text-sm font-medium text-foreground">
                                <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-input" @checked(old('is_active', $benchmark?->is_active ?? true))>
                                Benchmark is active
                            </label>
                        </div>
                    </div>

                    @include('admin.pricing.partials.form-actions', [
                        'submitLabel' => $isEditing ? 'Save changes' : 'Create benchmark',
                        'cancelRoute' => route('admin.pricing.benchmarks.index'),
                        'deleteRoute' => $isEditing ? route('admin.pricing.benchmarks.destroy', $benchmark) : null,
                    ])
                </form>
            </div>
        </div>
    </section>
@endsection
