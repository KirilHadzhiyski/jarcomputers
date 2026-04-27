<div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
        @isset($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">{{ $eyebrow }}</p>
        @endisset

        <h1 class="section-heading">{{ $title }}</h1>

        @isset($description)
            <p class="section-copy">{{ $description }}</p>
        @endisset
    </div>

    @isset($actions)
        <div class="flex flex-wrap gap-3">
            {!! $actions !!}
        </div>
    @endisset
</div>
