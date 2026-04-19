@php($items = $items ?? config('site.trust_items'))
<section class="border-y border-slate-200/70 bg-white">
    <div class="site-container py-5">
        <div class="trust-grid">
            @foreach ($items as $item)
                <div class="trust-grid-item">
                    {{ $item['text'] }}
                </div>
            @endforeach
        </div>
    </div>
</section>
