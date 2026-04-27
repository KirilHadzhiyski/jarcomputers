@extends('layouts.site')

@php
    $source = $source ?? null;
    $isEditing = filled($source?->id);
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Pricing',
                'title' => $isEditing ? "Edit {$source->name}" : 'New source',
                'description' => 'Attach benchmark channels to a market and define whether they are manual, scraper, or hybrid inputs.',
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEditing ? route('admin.pricing.sources.update', $source) : route('admin.pricing.sources.store') }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="pricing_market_id" class="text-sm font-medium text-foreground">Market</label>
                            <select id="pricing_market_id" name="pricing_market_id" class="input-shell" required>
                                @foreach ($markets as $market)
                                    <option value="{{ $market->id }}" @selected((string) old('pricing_market_id', $source?->pricing_market_id) === (string) $market->id)>{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="name" class="text-sm font-medium text-foreground">Source name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $source?->name) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="source_key" class="text-sm font-medium text-foreground">Source key</label>
                            <input id="source_key" name="source_key" type="text" value="{{ old('source_key', $source?->source_key) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="input_type" class="text-sm font-medium text-foreground">Input type</label>
                            <select id="input_type" name="input_type" class="input-shell" required>
                                @foreach ($inputTypeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('input_type', $source?->input_type ?? 'hybrid') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="base_url" class="text-sm font-medium text-foreground">Base URL</label>
                            <input id="base_url" name="base_url" type="url" value="{{ old('base_url', $source?->base_url) }}" class="input-shell">
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="notes" class="text-sm font-medium text-foreground">Notes</label>
                            <textarea id="notes" name="notes" rows="5" class="input-shell h-auto">{{ old('notes', $source?->notes) }}</textarea>
                        </div>
                        <div class="admin-field admin-field-span">
                            <label class="flex items-center gap-3 text-sm font-medium text-foreground">
                                <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-input" @checked(old('is_active', $source?->is_active ?? true))>
                                Source is active
                            </label>
                        </div>
                    </div>

                    @include('admin.pricing.partials.form-actions', [
                        'submitLabel' => $isEditing ? 'Save changes' : 'Create source',
                        'cancelRoute' => route('admin.pricing.sources.index'),
                        'deleteRoute' => $isEditing ? route('admin.pricing.sources.destroy', $source) : null,
                    ])
                </form>
            </div>
        </div>
    </section>
@endsection
