@extends('layouts.site')

@php
    $tableFields = collect($definition['fields'])->filter(fn ($field) => $field['table'])->values();
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                @include('admin.partials.shell-header', [
                    'eyebrow' => 'Admin / Business',
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                ])
                <a href="{{ route('admin.business.create', $resource) }}" class="btn-primary">New {{ $definition['singular'] }}</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                @include('partials.flash')

                <form method="GET" action="{{ route('admin.business.index', $resource) }}" class="mb-6 flex flex-col gap-3 md:flex-row">
                    <input name="search" value="{{ $search }}" class="input-shell md:max-w-sm" placeholder="Search {{ strtolower($definition['title']) }}...">
                    <button type="submit" class="btn-secondary">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.business.index', $resource) }}" class="btn-secondary">Clear</a>
                    @endif
                </form>

                @if ($records->isEmpty())
                    <div class="admin-empty-state">
                        No {{ strtolower($definition['title']) }} records yet. Create the first {{ $definition['singular'] }} to activate this part of the backoffice.
                    </div>
                @else
                    <div class="admin-table-wrap">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-muted-foreground">
                                <tr>
                                    @foreach ($tableFields as $field)
                                        <th class="px-4 py-3">{{ $field['label'] }}</th>
                                    @endforeach
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records as $record)
                                    <tr class="border-t border-border/60">
                                        @foreach ($tableFields as $field)
                                            @php
                                                $value = data_get($record, $field['name']);
                                                $display = $value;

                                                if ($field['type'] === 'select') {
                                                    $display = ($field['options'][$value] ?? $value) ?: '-';
                                                } elseif ($field['type'] === 'checkbox') {
                                                    $display = $value ? 'Yes' : 'No';
                                                } elseif ($value instanceof DateTimeInterface) {
                                                    $display = $field['type'] === 'date' ? $value->format('d.m.Y') : $value->format('d.m.Y H:i');
                                                } elseif (is_numeric($value) && str_contains($field['name'], 'amount')) {
                                                    $display = number_format((float) $value, 2);
                                                } elseif (is_numeric($value) && (str_contains($field['name'], 'price') || str_contains($field['name'], 'cost') || str_contains($field['name'], 'total'))) {
                                                    $display = number_format((float) $value, 2);
                                                }
                                            @endphp
                                            <td class="px-4 py-3">
                                                @if ($field['name'] === 'status')
                                                    <span class="status-pill status-pill-{{ $value }}">{{ $display }}</span>
                                                @elseif ($field['type'] === 'checkbox')
                                                    <span class="status-pill {{ $value ? 'status-pill-approved' : 'status-pill-draft' }}">{{ $display }}</span>
                                                @else
                                                    {{ is_string($display) ? \Illuminate\Support\Str::limit($display, 80) : $display }}
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.business.edit', [$resource, $record]) }}" class="font-medium text-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
