@php
    $aggregate = $aggregateReview ?? \App\Support\SiteData::aggregateReview();
    $platforms = $reviewPlatforms ?? \App\Support\SiteData::reviewPlatforms();
    $primaryPlatform = collect($platforms)->firstWhere('primary', true) ?? $platforms[0] ?? null;
    $scanDate = ! empty($aggregate['scan_date']) ? \Illuminate\Support\Carbon::parse($aggregate['scan_date'])->format('d.m.Y') : null;
@endphp
<section id="{{ $sectionId ?? null }}" class="page-section {{ $sectionClass ?? 'section-soft' }}">
    <div class="site-container">
        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="card-soft">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $eyebrow ?? 'Оценки и репутация' }}</p>
                <p class="mt-4 text-5xl font-bold text-slate-950">{{ number_format((float) $aggregate['rating_value'], 1) }}/{{ $aggregate['rating_scale'] }}</p>
                <p class="mt-4 text-base leading-7 text-slate-600">
                    {{ $aggregate['reviews_count'] }} комбинирани оценки от наличните публични платформи.
                    @if ($scanDate)
                        Последен snapshot: {{ $scanDate }}.
                    @endif
                </p>
                <a href="{{ $aggregate['source_url'] }}" class="btn-secondary-dark mt-6" target="_blank" rel="noreferrer">
                    Виж източника
                </a>
                @if ($primaryPlatform)
                    <a href="{{ $primaryPlatform['source_url'] }}" class="btn-primary mt-3" target="_blank" rel="noreferrer">
                        Виж Google отзивите
                    </a>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($platforms as $platform)
                    <a href="{{ $platform['source_url'] }}" class="card-service block" target="_blank" rel="noreferrer">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $platform['label'] }}</p>
                        <p class="mt-4 text-4xl font-bold text-slate-950">
                            {{ number_format((float) $platform['rating_value'], 1) }}/{{ $platform['rating_scale'] }}
                        </p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            {{ $platform['reviews_count'] }} оценки
                            @if (! empty($platform['scan_date']))
                                · snapshot {{ \Illuminate\Support\Carbon::parse($platform['scan_date'])->format('d.m.Y') }}
                            @endif
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
