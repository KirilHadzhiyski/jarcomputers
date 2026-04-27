@extends('layouts.site')

@php
    $configuration = $configuration ?? null;
    $isEditing = filled($configuration?->id);
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Pricing',
                'title' => $isEditing ? "Edit {$configuration->name}" : 'New pricing configuration',
                'description' => 'Capture the internal baseline data used to compare each configuration against external market benchmarks.',
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEditing ? route('admin.pricing.configurations.update', $configuration) : route('admin.pricing.configurations.store') }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="name" class="text-sm font-medium text-foreground">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $configuration?->name) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="sku" class="text-sm font-medium text-foreground">SKU</label>
                            <input id="sku" name="sku" type="text" value="{{ old('sku', $configuration?->sku) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="base_price_bgn" class="text-sm font-medium text-foreground">Base price (BGN)</label>
                            <input id="base_price_bgn" name="base_price_bgn" type="number" step="0.01" min="0" value="{{ old('base_price_bgn', $configuration?->base_price_bgn) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="status" class="text-sm font-medium text-foreground">Status</label>
                            <select id="status" name="status" class="input-shell" required>
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $configuration?->status ?? 'draft') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="description" class="text-sm font-medium text-foreground">Description</label>
                            <textarea id="description" name="description" rows="4" class="input-shell h-auto">{{ old('description', $configuration?->description) }}</textarea>
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="component_summary" class="text-sm font-medium text-foreground">Component summary</label>
                            <textarea id="component_summary" name="component_summary" rows="4" class="input-shell h-auto">{{ old('component_summary', $configuration?->component_summary) }}</textarea>
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="notes" class="text-sm font-medium text-foreground">Internal notes</label>
                            <textarea id="notes" name="notes" rows="5" class="input-shell h-auto">{{ old('notes', $configuration?->notes) }}</textarea>
                        </div>
                    </div>

                    @include('admin.pricing.partials.form-actions', [
                        'submitLabel' => $isEditing ? 'Save changes' : 'Create configuration',
                        'cancelRoute' => route('admin.pricing.configurations.index'),
                        'deleteRoute' => $isEditing ? route('admin.pricing.configurations.destroy', $configuration) : null,
                    ])
                </form>
            </div>
        </div>
    </section>
@endsection
