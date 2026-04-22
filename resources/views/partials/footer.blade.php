@php($site = config('site'))
@php($brandLogo = asset('images/branding/jar-computers-logo-blue.svg').'?v='.filemtime(public_path('images/branding/jar-computers-logo-blue.svg')))
<footer class="section-dark mt-16 border-t border-white/10">
    <div class="site-container py-14">
        <div class="grid gap-10 lg:grid-cols-4">
            <div class="space-y-4">
                <div>
                    <a href="{{ route('home') }}" class="footer-brand-lockup" aria-label="{{ $site['brand'] }}">
                        <img
                            src="{{ $brandLogo }}"
                            alt="{{ $site['brand'] }}"
                            class="footer-brand-logo-image"
                        >
                    </a>
                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        Професионален ремонт на iPhone с гаранция, проследима комуникация и куриерска услуга в цяла България.
                    </p>
                </div>
                <div class="space-y-2 text-sm text-slate-300">
                    <a class="block hover:text-white" href="tel:{{ $site['phone_href'] }}">{{ $site['phone'] }}</a>
                    <a class="block hover:text-white" href="tel:{{ $site['landline_href'] }}">{{ $site['landline'] }}</a>
                    <a class="block hover:text-white" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>
                    <p>{{ $site['address'] }}</p>
                </div>
            </div>

            <div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Услуги</p>
                <div class="space-y-2 text-sm text-slate-300">
                    <a class="block hover:text-white" href="{{ route('main-service') }}">Ремонт на iPhone</a>
                    @foreach ($site['services'] as $service)
                        <a class="block hover:text-white" href="{{ url($service['slug']) }}">{{ $service['name'] }}</a>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Социални мрежи</p>
                <div class="space-y-2 text-sm text-slate-300">
                    @foreach ($site['socials'] as $social)
                        <a class="block hover:text-white" href="{{ $social['href'] }}" target="_blank" rel="noreferrer">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Информация</p>
                <div class="space-y-2 text-sm text-slate-300">
                    <a class="block hover:text-white" href="{{ route('about') }}">За нас</a>
                    <a class="block hover:text-white" href="{{ route('faq') }}">Често задавани въпроси</a>
                    <a class="block hover:text-white" href="{{ route('pricing') }}">Цени</a>
                    <a class="block hover:text-white" href="{{ route('contact') }}">Контакти</a>
                    <a class="block hover:text-white" href="{{ route('privacy') }}">{{ $site['legal']['privacy_label'] }}</a>
                    <a class="block hover:text-white" href="{{ route('terms') }}">{{ $site['legal']['terms_label'] }}</a>
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $site['brand'] }}. Всички права запазени.</p>
            <p>Физически обект в Благоевград · Курирерска услуга за цяла България</p>
        </div>
    </div>
</footer>
