@php($site = config('site'))
<section class="hero-section page-section">
    <div class="site-container text-center">
        <h2 class="mx-auto max-w-4xl text-3xl font-bold text-balance md:text-4xl">
            {{ $title ?? 'Поръчай ремонт още днес с JAR Computers Благоевград' }}
        </h2>
        <p class="mx-auto mt-4 max-w-3xl text-base leading-7 text-[var(--hero-muted)] md:text-lg">
            {{ $subtitle ?? 'Безплатна диагностика, куриер в двете посоки и гаранция до 12 месеца.' }}
        </p>
        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('contact') }}#repair-form" class="btn-primary">
                Поръчай ремонт
            </a>
            <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">
                Обади се
            </a>
        </div>
    </div>
</section>
