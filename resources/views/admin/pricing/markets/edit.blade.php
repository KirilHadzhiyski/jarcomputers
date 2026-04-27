@extends('layouts.site')

@php
    $market = $market ?? null;
    $isEditing = filled($market?->id);
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Pricing',
                'title' => $isEditing ? "Edit {$market->name}" : 'New market',
                'description' => 'Define VAT and currency assumptions used for the cross-border pricing engine.',
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEditing ? route('admin.pricing.markets.update', $market) : route('admin.pricing.markets.store') }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="name" class="text-sm font-medium text-foreground">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $market?->name) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="code" class="text-sm font-medium text-foreground">Code</label>
                            <input id="code" name="code" type="text" value="{{ old('code', $market?->code) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="currency_code" class="text-sm font-medium text-foreground">Currency</label>
                            <input id="currency_code" name="currency_code" type="text" value="{{ old('currency_code', $market?->currency_code ?? 'EUR') }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="vat_rate" class="text-sm font-medium text-foreground">VAT rate</label>
                            <input id="vat_rate" name="vat_rate" type="number" step="0.01" min="0" max="100" value="{{ old('vat_rate', $market?->vat_rate) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field">
                            <label for="exchange_rate_to_bgn" class="text-sm font-medium text-foreground">Exchange rate to BGN</label>
                            <input id="exchange_rate_to_bgn" name="exchange_rate_to_bgn" type="number" step="0.0001" min="0.0001" value="{{ old('exchange_rate_to_bgn', $market?->exchange_rate_to_bgn ?? 1) }}" class="input-shell" required>
                        </div>
                        <div class="admin-field justify-end">
                            <label class="flex items-center gap-3 pt-8 text-sm font-medium text-foreground">
                                <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-input" @checked(old('is_active', $market?->is_active ?? true))>
                                Active market
                            </label>
                        </div>
                        <div class="admin-field admin-field-span">
                            <label for="notes" class="text-sm font-medium text-foreground">Notes</label>
                            <textarea id="notes" name="notes" rows="5" class="input-shell h-auto">{{ old('notes', $market?->notes) }}</textarea>
                        </div>
                    </div>

                    @include('admin.pricing.partials.form-actions', [
                        'submitLabel' => $isEditing ? 'Save changes' : 'Create market',
                        'cancelRoute' => route('admin.pricing.markets.index'),
                        'deleteRoute' => $isEditing ? route('admin.pricing.markets.destroy', $market) : null,
                    ])
                </form>
            </div>
        </div>
    </section>
@endsection
