@extends('layouts.site')

@php
    $isEditing = filled($record?->id);
@endphp

@section('content')
    <section class="page-section">
        <div class="site-container">
            @include('admin.partials.shell-header', [
                'eyebrow' => 'Admin / Business',
                'title' => $isEditing ? 'Edit '.$definition['singular'] : 'New '.$definition['singular'],
                'description' => $definition['description'],
            ])

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft">
                <form method="POST" action="{{ $isEditing ? route('admin.business.update', [$resource, $record]) : route('admin.business.store', $resource) }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="admin-form-grid">
                        @foreach ($definition['fields'] as $field)
                            @php
                                $name = $field['name'];
                                $rawValue = old($name, $record?->{$name});
                                $isNullable = in_array('nullable', $field['rules'], true);

                                if ($rawValue instanceof DateTimeInterface) {
                                    $rawValue = $field['type'] === 'date' ? $rawValue->format('Y-m-d') : $rawValue->format('Y-m-d\TH:i');
                                }
                            @endphp

                            <div class="admin-field {{ $field['span'] ? 'admin-field-span' : '' }}">
                                @if ($field['type'] === 'checkbox')
                                    <label class="flex items-center gap-3 rounded-2xl border border-border/60 bg-background/70 px-4 py-3 text-sm font-medium text-foreground">
                                        <input type="checkbox" name="{{ $name }}" value="1" @checked((bool) $rawValue) class="size-4 rounded border-border text-primary">
                                        {{ $field['label'] }}
                                    </label>
                                @else
                                    <label for="{{ $name }}" class="text-sm font-medium text-foreground">{{ $field['label'] }}</label>

                                    @if ($field['type'] === 'textarea')
                                        <textarea id="{{ $name }}" name="{{ $name }}" rows="5" class="input-shell h-auto">{{ $rawValue }}</textarea>
                                    @elseif ($field['type'] === 'select')
                                        <select id="{{ $name }}" name="{{ $name }}" class="input-shell">
                                            @if ($isNullable)
                                                <option value="">None</option>
                                            @endif
                                            @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                                <option value="{{ $optionValue }}" @selected((string) $rawValue === (string) $optionValue)>{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            id="{{ $name }}"
                                            name="{{ $name }}"
                                            type="{{ $field['type'] }}"
                                            value="{{ $rawValue }}"
                                            @if ($field['step']) step="{{ $field['step'] }}" @endif
                                            class="input-shell"
                                        >
                                    @endif
                                @endif

                                @error($name)
                                    <p class="text-sm text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-col gap-3 border-t border-border/60 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex gap-3">
                            <button type="submit" class="btn-primary">{{ $isEditing ? 'Save changes' : 'Create '.$definition['singular'] }}</button>
                            <a href="{{ route('admin.business.index', $resource) }}" class="btn-secondary">Cancel</a>
                        </div>

                        @if ($isEditing)
                            <button
                                type="submit"
                                form="delete-business-record"
                                class="inline-flex items-center justify-center rounded-md border border-rose-500/25 bg-rose-500/10 px-5 py-2.5 text-sm font-medium text-rose-700"
                                onclick="return confirm('Delete this record?')"
                            >
                                Delete
                            </button>
                        @endif
                    </div>
                </form>

                @if ($isEditing)
                    <form id="delete-business-record" method="POST" action="{{ route('admin.business.destroy', [$resource, $record]) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
