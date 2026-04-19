<section class="page-section">
    <div class="site-container max-w-4xl">
        @if (($title ?? 'Често задавани въпроси') !== '')
            <div class="text-center">
                <h2 class="section-heading">{{ $title ?? 'Често задавани въпроси' }}</h2>
            </div>
        @endif

        <div class="mt-8 space-y-3">
            @foreach ($items as $item)
                <details class="faq-item" @if ($loop->first && ($openFirst ?? false)) open @endif>
                    <summary>{{ $item['q'] }}</summary>
                    <div class="faq-answer">
                        {{ $item['a'] }}
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
