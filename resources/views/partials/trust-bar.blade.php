@php($items = $items ?? config('site.trust_items'))
<section class="border-y border-slate-200/70 bg-white">
    <div class="site-container py-5">
        <div class="trust-grid">
            @foreach ($items as $item)
                @if (! empty($item['href']))
                    <a
                        href="{{ $item['href'] }}"
                        class="trust-grid-item trust-grid-item-link"
                        @if (! empty($item['external'])) target="_blank" rel="noreferrer" @endif
                    >
                        {{ $item['text'] }}
                    </a>
                @else
                    <div class="trust-grid-item">
                        {{ $item['text'] }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
