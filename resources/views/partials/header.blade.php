@php($site = config('site'))

<header class="site-header">
    <div class="site-container flex min-h-16 items-center justify-between gap-4 py-3">
        <a href="{{ route('home') }}" class="brand-lockup" aria-label="{{ $site['brand'] }}">
            <span class="brand-copy">
                <span class="brand-title">
                    <span class="brand-title-jar">JAR</span>
                    <span class="brand-title-computers">Computers</span>
                </span>
                <span class="brand-subtitle">{{ $site['city_name'] }}</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex">
            @foreach ($site['navigation'] as $item)
                @php($isActive = $item['href'] === '/' ? request()->path() === '/' : request()->is(ltrim($item['href'], '/')))
                <a href="{{ url($item['href']) }}" class="nav-link {{ $isActive ? 'nav-link-active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 sm:flex">
            <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary">
                {{ $site['phone'] }}
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-secondary-dark">
                    Профил
                </a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark">
                        Admin
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-primary">Изход</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-secondary-dark">
                    Вход
                </a>
                <a href="{{ route('register') }}" class="btn-primary">
                    Регистрация
                </a>
            @endauth
        </div>

        <details class="relative lg:hidden">
            <summary class="list-none rounded-md border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm">
                Меню
            </summary>

            <div class="absolute right-0 mt-3 flex w-72 flex-col gap-2 rounded-xl border bg-background p-4 shadow-lg">
                <nav class="flex flex-col gap-2">
                    @foreach ($site['navigation'] as $item)
                        <a href="{{ url($item['href']) }}" class="nav-link justify-center">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <a href="tel:{{ $site['phone_href'] }}" class="btn-secondary mt-2 justify-center">
                    {{ $site['phone'] }}
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn-secondary-dark justify-center">
                        Профил
                    </a>

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark justify-center">
                            Admin панел
                        </a>
                    @endif

                    <a href="{{ route('tickets.create') }}" class="btn-primary justify-center">
                        Нов билет
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary-dark w-full justify-center">Изход</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary-dark justify-center">
                        Вход
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary justify-center">
                        Регистрация
                    </a>
                @endauth
            </div>
        </details>
    </div>
</header>
